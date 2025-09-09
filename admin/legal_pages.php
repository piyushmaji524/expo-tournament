<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_page'])) {
        $pageId = (int)$_POST['page_id'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($title) || empty($content)) {
            $message = 'Title and content are required.';
            $messageType = 'error';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE legal_pages SET title = ?, content = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$title, $content, $isActive, $pageId]);
                
                $message = 'Legal page updated successfully!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Failed to update page: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['add_page'])) {
        $pageKey = trim($_POST['page_key']);
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($pageKey) || empty($title) || empty($content)) {
            $message = 'All fields are required.';
            $messageType = 'error';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO legal_pages (page_key, title, content, is_active) VALUES (?, ?, ?, ?)");
                $stmt->execute([$pageKey, $title, $content, $isActive]);
                
                $message = 'New legal page created successfully!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Failed to create page: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['delete_page'])) {
        $pageId = (int)$_POST['page_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM legal_pages WHERE id = ?");
            $stmt->execute([$pageId]);
            
            $message = 'Legal page deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Failed to delete page: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all legal pages
$stmt = $pdo->query("SELECT * FROM legal_pages ORDER BY page_key ASC");
$legalPages = $stmt->fetchAll();

// Get current editing page
$editingPage = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM legal_pages WHERE id = ?");
    $stmt->execute([$editId]);
    $editingPage = $stmt->fetch();
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold flex items-center">
            <i class="fas fa-gavel mr-3 text-highlight"></i>
            Legal Pages Management
        </h1>
        <button onclick="showAddForm()" class="bg-highlight hover:bg-red-600 transition px-4 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Add New Page
        </button>
    </div>

    <?php if ($message): ?>
        <div class="bg-<?php echo $messageType == 'success' ? 'green' : 'red'; ?>-900 border border-<?php echo $messageType == 'success' ? 'green' : 'red'; ?>-700 rounded-lg p-4">
            <p class="text-<?php echo $messageType == 'success' ? 'green' : 'red'; ?>-300"><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <div id="pageForm" class="bg-secondary rounded-lg p-6 <?php echo $editingPage || isset($_GET['add']) ? '' : 'hidden'; ?>">
        <h2 class="text-xl font-semibold mb-4">
            <?php echo $editingPage ? 'Edit Legal Page' : 'Add New Legal Page'; ?>
        </h2>
        
        <form method="POST" class="space-y-4">
            <?php if ($editingPage): ?>
                <input type="hidden" name="page_id" value="<?php echo $editingPage['id']; ?>">
            <?php endif; ?>
            
            <?php if (!$editingPage): ?>
                <div>
                    <label class="block text-sm font-medium mb-2">Page Key (URL identifier)</label>
                    <input type="text" name="page_key" required 
                           class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none"
                           placeholder="e.g., terms_of_service, privacy_policy">
                    <p class="text-xs text-gray-400 mt-1">Use lowercase letters, numbers, and underscores only</p>
                </div>
            <?php endif; ?>
            
            <div>
                <label class="block text-sm font-medium mb-2">Page Title</label>
                <input type="text" name="title" required 
                       value="<?php echo htmlspecialchars($editingPage['title'] ?? ''); ?>"
                       class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none"
                       placeholder="Enter page title">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Content (HTML allowed)</label>
                <div class="relative">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex space-x-2">
                            <button type="button" onclick="clearContent()" class="text-xs bg-gray-600 hover:bg-gray-700 px-2 py-1 rounded">
                                <i class="fas fa-trash mr-1"></i>Clear
                            </button>
                            <button type="button" onclick="pasteFromClipboard()" class="text-xs bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded">
                                <i class="fas fa-paste mr-1"></i>Paste
                            </button>
                            <button type="button" onclick="selectAllContent()" class="text-xs bg-gray-600 hover:bg-gray-700 px-2 py-1 rounded">
                                <i class="fas fa-check-square mr-1"></i>Select All
                            </button>
                        </div>
                        <span class="text-xs text-gray-400" id="charCount">0 characters</span>
                    </div>
                    <textarea name="content" id="contentEditor" rows="20" required 
                              class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none font-mono text-sm"
                              placeholder="Enter page content (HTML tags allowed)"
                              oninput="updateCharCount()"
                              onpaste="handlePaste(event)"><?php echo htmlspecialchars($editingPage['content'] ?? ''); ?></textarea>
                </div>
                <p class="text-xs text-gray-400 mt-1">You can use HTML tags like &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, etc. Use Ctrl+V to paste content.</p>
            </div>
            
            <div class="flex items-center space-x-3">
                <input type="checkbox" name="is_active" id="is_active" 
                       <?php echo ($editingPage['is_active'] ?? 1) ? 'checked' : ''; ?>
                       class="w-4 h-4 text-highlight bg-accent border-gray-600 rounded focus:ring-highlight">
                <label for="is_active" class="text-sm">Page is active and visible to users</label>
            </div>
            
            <div class="flex space-x-3">
                <button type="submit" name="<?php echo $editingPage ? 'update_page' : 'add_page'; ?>" 
                        class="bg-highlight hover:bg-red-600 transition px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i><?php echo $editingPage ? 'Update Page' : 'Create Page'; ?>
                </button>
                <button type="button" onclick="hideForm()" 
                        class="bg-gray-600 hover:bg-gray-700 transition px-6 py-2 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Legal Pages List -->
    <div class="bg-secondary rounded-lg">
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-xl font-semibold">Legal Pages</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-accent">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Page Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Last Updated</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if (empty($legalPages)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                <i class="fas fa-file-contract text-3xl mb-2"></i>
                                <p>No legal pages found</p>
                                <p class="text-sm">Create your first legal page to get started</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($legalPages as $page): ?>
                            <tr class="hover:bg-accent transition">
                                <td class="px-6 py-4 font-mono text-sm text-blue-400">
                                    <?php echo htmlspecialchars($page['page_key']); ?>
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    <?php echo htmlspecialchars($page['title']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs <?php echo $page['is_active'] ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300'; ?>">
                                        <?php echo $page['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-400 text-sm">
                                    <?php echo date('M j, Y g:i A', strtotime($page['updated_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="?edit=<?php echo $page['id']; ?>" 
                                           class="bg-blue-600 hover:bg-blue-700 transition px-3 py-1 rounded text-sm">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </a>
                                        <a href="../legal.php?page=<?php echo $page['page_key']; ?>" target="_blank"
                                           class="bg-green-600 hover:bg-green-700 transition px-3 py-1 rounded text-sm">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this page?')">
                                            <input type="hidden" name="page_id" value="<?php echo $page['id']; ?>">
                                            <button type="submit" name="delete_page" 
                                                    class="bg-red-600 hover:bg-red-700 transition px-3 py-1 rounded text-sm">
                                                <i class="fas fa-trash mr-1"></i>Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($legalPages as $page): ?>
                <?php if ($page['is_active']): ?>
                    <a href="../legal.php?page=<?php echo $page['page_key']; ?>" target="_blank"
                       class="bg-accent hover:bg-highlight transition p-3 rounded-lg text-center">
                        <i class="fas fa-external-link-alt text-lg mb-2"></i>
                        <div class="text-sm"><?php echo htmlspecialchars($page['title']); ?></div>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function showAddForm() {
    document.getElementById('pageForm').classList.remove('hidden');
    window.location.hash = 'pageForm';
    updateCharCount();
}

function hideForm() {
    document.getElementById('pageForm').classList.add('hidden');
    window.location.href = window.location.pathname;
}

// Enhanced paste functionality
function pasteFromClipboard() {
    const textarea = document.getElementById('contentEditor');
    
    if (navigator.clipboard && navigator.clipboard.readText) {
        navigator.clipboard.readText().then(text => {
            if (text) {
                // Insert at cursor position
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const before = textarea.value.substring(0, start);
                const after = textarea.value.substring(end);
                textarea.value = before + text + after;
                
                // Set cursor position after pasted content
                textarea.selectionStart = textarea.selectionEnd = start + text.length;
                textarea.focus();
                updateCharCount();
                
                // Show success message
                showToast('Content pasted successfully!', 'success');
            }
        }).catch(err => {
            console.log('Clipboard read failed:', err);
            showToast('Please use Ctrl+V to paste content', 'info');
        });
    } else {
        // Fallback for browsers without clipboard API
        textarea.focus();
        showToast('Please use Ctrl+V to paste content', 'info');
    }
}

function clearContent() {
    if (confirm('Are you sure you want to clear all content?')) {
        document.getElementById('contentEditor').value = '';
        updateCharCount();
        showToast('Content cleared', 'info');
    }
}

function selectAllContent() {
    const textarea = document.getElementById('contentEditor');
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length);
    showToast('All content selected', 'info');
}

function updateCharCount() {
    const textarea = document.getElementById('contentEditor');
    const charCount = document.getElementById('charCount');
    if (textarea && charCount) {
        const count = textarea.value.length;
        charCount.textContent = count.toLocaleString() + ' characters';
    }
}

function handlePaste(event) {
    // Allow default paste behavior
    setTimeout(() => {
        updateCharCount();
        showToast('Content pasted!', 'success');
    }, 100);
}

function showToast(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white text-sm transition-all duration-300 ${
        type === 'success' ? 'bg-green-600' :
        type === 'error' ? 'bg-red-600' :
        'bg-blue-600'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => toast.style.opacity = '1', 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize character count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCharCount();
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            const activeElement = document.activeElement;
            if (activeElement && activeElement.id === 'contentEditor') {
                switch(e.key) {
                    case 'a':
                        // Ctrl+A already handled by browser
                        break;
                    case 's':
                        e.preventDefault();
                        // Find and click save button
                        const saveBtn = document.querySelector('button[name="update_page"], button[name="add_page"]');
                        if (saveBtn) {
                            showToast('Saving...', 'info');
                            saveBtn.click();
                        }
                        break;
                }
            }
        }
    });
});

// Show form if editing or adding
<?php if ($editingPage || isset($_GET['add'])): ?>
    document.getElementById('pageForm').classList.remove('hidden');
    setTimeout(updateCharCount, 100);
<?php endif; ?>
</script>

<?php include 'common/bottom.php'; ?>
