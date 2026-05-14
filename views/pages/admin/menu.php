<?php
/**
 * Admin Menu Items Page
 * Variables: $pageTitle, $currentPage, $menuItems
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Menu Items</h1>
        <p>Manage navigation menu items</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/menu/create" class="btn btn-primary">
            <i class="icon-plus"></i> Add Menu Item
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Menu Items</h3>
        <small class="text-muted">Drag items to reorder or update sort_order values</small>
    </div>
    <div class="card-body">
        <?php if (!empty($menuItems)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table" id="menu-items-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Label</th>
                        <th>Icon</th>
                        <th>URL</th>
                        <th>Bottom Nav</th>
                        <th>Bottom Sheet</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menuItems as $item): ?>
                    <tr data-id="<?= (int)$item['id'] ?>">
                        <td>
                            <span class="sort-handle" style="cursor: move; user-select: none;">&#9776;</span>
                            <span class="sort-order"><?= (int)($item['sort_order'] ?? 0) ?></span>
                        </td>
                        <td><strong><?= htmlspecialchars($item['label'] ?? '') ?></strong></td>
                        <td>
                            <?php if (!empty($item['icon'])): ?>
                            <i class="icon-<?= htmlspecialchars($item['icon']) ?>"></i>
                            <code><?= htmlspecialchars($item['icon']) ?></code>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><code><?= htmlspecialchars($item['url'] ?? '#') ?></code></td>
                        <td>
                            <?php if (!empty($item['show_in_bottom_nav'])): ?>
                            <span class="badge badge-info">Yes</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($item['show_in_bottom_sheet'])): ?>
                            <span class="badge badge-info">Yes</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($item['is_active'])): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/menu/<?= (int)$item['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="icon-edit"></i>
                                </a>
                                <form action="/admin/menu/<?= (int)$item['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this menu item?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="icon-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <button type="button" id="save-order-btn" class="btn btn-success" style="display: none;">
                <i class="icon-save"></i> Save New Order
            </button>
        </div>
        <?php else: ?>
        <p class="text-muted">No menu items found. <a href="/admin/menu/create">Add your first menu item</a>.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Simple drag-and-drop reordering
(function() {
    var table = document.getElementById('menu-items-table');
    if (!table) return;
    
    var tbody = table.querySelector('tbody');
    var saveBtn = document.getElementById('save-order-btn');
    var draggedRow = null;
    var orderChanged = false;

    tbody.addEventListener('dragstart', function(e) {
        if (e.target.tagName === 'TR') {
            draggedRow = e.target;
            e.target.style.opacity = '0.5';
        }
    });

    tbody.addEventListener('dragend', function(e) {
        if (e.target.tagName === 'TR') {
            e.target.style.opacity = '';
            draggedRow = null;
        }
    });

    tbody.addEventListener('dragover', function(e) {
        e.preventDefault();
        var targetRow = e.target.closest('tr');
        if (targetRow && draggedRow && targetRow !== draggedRow) {
            var bounding = targetRow.getBoundingClientRect();
            var offset = e.clientY - bounding.top;
            if (offset > bounding.height / 2) {
                tbody.insertBefore(draggedRow, targetRow.nextSibling);
            } else {
                tbody.insertBefore(draggedRow, targetRow);
            }
            orderChanged = true;
            saveBtn.style.display = '';
        }
    });

    // Make rows draggable
    var rows = tbody.querySelectorAll('tr');
    rows.forEach(function(row) {
        row.draggable = true;
    });

    // Save order button click
    saveBtn.addEventListener('click', function() {
        var items = {};
        var rows = tbody.querySelectorAll('tr');
        rows.forEach(function(row, index) {
            var id = row.dataset.id;
            items[id] = index;
            row.querySelector('.sort-order').textContent = index;
        });

        fetch('/admin/menu/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ items: items })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                saveBtn.style.display = 'none';
                orderChanged = false;
                alert('Menu order saved successfully!');
            } else {
                alert('Failed to save order: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(function(err) {
            alert('Failed to save order: ' + err.message);
        });
    });
})();
</script>
