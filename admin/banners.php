<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Handle banner operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_banner'])) {
        $title = trim($_POST['title']);
        $link_url = trim($_POST['link_url']);
        $display_order = intval($_POST['display_order']);
        
        if (empty($title)) {
            $message = 'Banner title is required.';
            $messageType = 'error';
        } elseif (!isset($_FILES['banner_image']) || $_FILES['banner_image']['error'] !== UPLOAD_ERR_OK) {
            $message = 'Please select a banner image.';
            $messageType = 'error';
        } else {
            $file = $_FILES['banner_image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            $max_size = 10 * 1024 * 1024; // 10MB
            
            if (!in_array($file['type'], $allowed_types)) {
                $message = 'Only JPEG, PNG, JPG, and WebP images are allowed.';
                $messageType = 'error';
            } elseif ($file['size'] > $max_size) {
                $message = 'Image size must be less than 10MB.';
                $messageType = 'error';
            } else {
                // Create upload directory if it doesn't exist
                $upload_dir = '../uploads/banners/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'banner_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                $file_path = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    // Resize image to 16:9 ratio
                    $resized_filename = resizeBannerImage($file_path, $upload_dir);
                    
                    if ($resized_filename) {
                        // Delete original if different from resized
                        if ($resized_filename !== $filename) {
                            unlink($file_path);
                            $filename = $resized_filename;
                        }
                        
                        // Save to database
                        $stmt = $pdo->prepare("INSERT INTO banners (title, image_path, link_url, display_order) VALUES (?, ?, ?, ?)");
                        if ($stmt->execute([$title, 'uploads/banners/' . $filename, $link_url, $display_order])) {
                            $message = 'Banner added successfully!';
                            $messageType = 'success';
                        } else {
                            $message = 'Failed to save banner to database.';
                            $messageType = 'error';
                            unlink($upload_dir . $filename);
                        }
                    } else {
                        $message = 'Failed to process banner image.';
                        $messageType = 'error';
                        unlink($file_path);
                    }
                } else {
                    $message = 'Failed to upload banner image.';
                    $messageType = 'error';
                }
            }
        }
    } elseif (isset($_POST['toggle_status'])) {
        $banner_id = intval($_POST['banner_id']);
        $new_status = intval($_POST['new_status']);
        
        $stmt = $pdo->prepare("UPDATE banners SET is_active = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $banner_id])) {
            $message = 'Banner status updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update banner status.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['delete_banner'])) {
        $banner_id = intval($_POST['banner_id']);
        
        // Get banner image path
        $stmt = $pdo->prepare("SELECT image_path FROM banners WHERE id = ?");
        $stmt->execute([$banner_id]);
        $banner = $stmt->fetch();
        
        if ($banner) {
            // Delete banner from database
            $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
            if ($stmt->execute([$banner_id])) {
                // Delete image file
                if (file_exists('../' . $banner['image_path'])) {
                    unlink('../' . $banner['image_path']);
                }
                $message = 'Banner deleted successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to delete banner.';
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['update_order'])) {
        $banner_id = intval($_POST['banner_id']);
        $new_order = intval($_POST['new_order']);
        
        $stmt = $pdo->prepare("UPDATE banners SET display_order = ? WHERE id = ?");
        if ($stmt->execute([$new_order, $banner_id])) {
            $message = 'Banner order updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update banner order.';
            $messageType = 'error';
        }
    }
}

// Function to resize banner image to 16:9 ratio
function resizeBannerImage($file_path, $upload_dir) {
    $image_info = getimagesize($file_path);
    if (!$image_info) return false;
    
    $target_width = 1920;
    $target_height = 1080; // 16:9 ratio
    
    // Create image resource based on type
    switch ($image_info['mime']) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($file_path);
            break;
        case 'image/png':
            $source = imagecreatefrompng($file_path);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($file_path);
            break;
        default:
            return false;
    }
    
    if (!$source) return false;
    
    $original_width = imagesx($source);
    $original_height = imagesy($source);
    
    // Calculate crop dimensions to maintain 16:9 ratio
    $original_ratio = $original_width / $original_height;
    $target_ratio = 16 / 9;
    
    if ($original_ratio > $target_ratio) {
        // Image is wider than 16:9, crop width
        $crop_height = $original_height;
        $crop_width = $crop_height * $target_ratio;
        $crop_x = ($original_width - $crop_width) / 2;
        $crop_y = 0;
    } else {
        // Image is taller than 16:9, crop height
        $crop_width = $original_width;
        $crop_height = $crop_width / $target_ratio;
        $crop_x = 0;
        $crop_y = ($original_height - $crop_height) / 2;
    }
    
    // Create new image
    $resized = imagecreatetruecolor($target_width, $target_height);
    
    // Preserve transparency for PNG
    if ($image_info['mime'] == 'image/png') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $target_width, $target_height, $transparent);
    }
    
    // Copy and resize
    imagecopyresampled(
        $resized, $source,
        0, 0, $crop_x, $crop_y,
        $target_width, $target_height, $crop_width, $crop_height
    );
    
    // Save resized image
    $filename = 'banner_' . time() . '_' . rand(1000, 9999) . '.jpg';
    $new_path = $upload_dir . $filename;
    
    $success = imagejpeg($resized, $new_path, 90);
    
    // Clean up
    imagedestroy($source);
    imagedestroy($resized);
    
    return $success ? $filename : false;
}

// Get all banners
$stmt = $pdo->query("SELECT * FROM banners ORDER BY display_order ASC, created_at DESC");
$banners = $stmt->fetchAll();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold">Banner Management</h2>
            <p class="text-gray-400">Manage homepage banners and carousel</p>
        </div>
        <div class="text-sm text-gray-400">
            Total Banners: <?php echo count($banners); ?>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="<?php echo $messageType == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> text-white p-4 rounded-lg text-center alert-message">
            <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Add New Banner -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-plus text-highlight mr-2"></i>
            Add New Banner
        </h3>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Banner Title *</label>
                    <input type="text" name="title" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter banner title">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Display Order</label>
                    <input type="number" name="display_order" min="0" value="0" class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Display order">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Link URL (Optional)</label>
                <input type="url" name="link_url" class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="https://example.com (optional)">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Banner Image *</label>
                <input type="file" name="banner_image" accept="image/*" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-highlight file:text-white hover:file:bg-red-600">
                <p class="text-xs text-gray-400 mt-1">Supported: JPG, PNG, WebP (max 10MB). Image will be auto-resized to 16:9 ratio (1920x1080)</p>
            </div>
            
            <button type="submit" name="add_banner" class="bg-highlight hover:bg-red-600 transition text-white font-bold py-3 px-6 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Add Banner
            </button>
        </form>
    </div>

    <!-- Existing Banners -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-images text-highlight mr-2"></i>
            Existing Banners
        </h3>

        <?php if (empty($banners)): ?>
            <div class="text-center py-8">
                <i class="fas fa-images text-4xl text-gray-500 mb-4"></i>
                <h4 class="text-lg font-semibold mb-2">No Banners</h4>
                <p class="text-gray-400">Add your first banner using the form above</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($banners as $banner): ?>
                    <div class="bg-accent rounded-lg p-4 border <?php echo $banner['is_active'] ? 'border-green-500' : 'border-gray-600'; ?>">
                        <!-- Banner Image -->
                        <div class="relative mb-4">
                            <img src="../<?php echo htmlspecialchars($banner['image_path']); ?>" alt="<?php echo htmlspecialchars($banner['title']); ?>" class="w-full h-32 object-cover rounded-lg">
                            <div class="absolute top-2 right-2">
                                <span class="bg-<?php echo $banner['is_active'] ? 'green' : 'red'; ?>-600 text-white text-xs px-2 py-1 rounded">
                                    <?php echo $banner['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Banner Info -->
                        <div class="mb-4">
                            <h4 class="font-semibold text-white mb-2"><?php echo htmlspecialchars($banner['title']); ?></h4>
                            <?php if ($banner['link_url']): ?>
                                <p class="text-xs text-blue-400 mb-2">
                                    <i class="fas fa-link mr-1"></i>
                                    <a href="<?php echo htmlspecialchars($banner['link_url']); ?>" target="_blank" class="hover:underline">
                                        <?php echo htmlspecialchars($banner['link_url']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-400">Order: <?php echo $banner['display_order']; ?></p>
                            <p class="text-xs text-gray-400">Created: <?php echo date('M j, Y', strtotime($banner['created_at'])); ?></p>
                        </div>
                        
                        <!-- Banner Actions -->
                        <div class="space-y-2">
                            <!-- Toggle Status -->
                            <form method="POST" class="inline">
                                <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                <input type="hidden" name="new_status" value="<?php echo $banner['is_active'] ? 0 : 1; ?>">
                                <button type="submit" name="toggle_status" class="w-full bg-<?php echo $banner['is_active'] ? 'yellow' : 'green'; ?>-600 hover:bg-<?php echo $banner['is_active'] ? 'yellow' : 'green'; ?>-700 transition text-white py-2 px-4 rounded text-sm">
                                    <i class="fas fa-<?php echo $banner['is_active'] ? 'pause' : 'play'; ?> mr-1"></i>
                                    <?php echo $banner['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                            
                            <!-- Update Order -->
                            <form method="POST" class="flex space-x-1">
                                <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                <input type="number" name="new_order" value="<?php echo $banner['display_order']; ?>" min="0" class="flex-1 bg-secondary border border-gray-600 rounded px-2 py-1 text-white text-sm">
                                <button type="submit" name="update_order" class="bg-blue-600 hover:bg-blue-700 transition text-white px-3 py-1 rounded text-sm">
                                    <i class="fas fa-sort"></i>
                                </button>
                            </form>
                            
                            <!-- Delete Banner -->
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this banner?')">
                                <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                <button type="submit" name="delete_banner" class="w-full bg-red-600 hover:bg-red-700 transition text-white py-2 px-4 rounded text-sm">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-hide success/error messages
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-message');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
});
</script>

<?php include 'common/bottom.php'; ?>
