 <?php
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$mode = 'list';
$product = ['id' => '', 'name' => '', 'price' => '', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$_POST['delete_id']]);
        $message = 'Product deleted successfully.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $price === '' || $description === '') {
            $message = 'Please fill in all fields.';
        } elseif (!is_numeric($price) || (float)$price < 0) {
            $message = 'Please enter a valid price.';
        } else {
            $id = $_POST['product_id'] ?? '';
            if ($id !== '') {
                $stmt = $pdo->prepare('UPDATE products SET name = ?, price = ?, description = ? WHERE id = ?');
                $stmt->execute([$name, $price, $description, $id]);
                $message = 'Product updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO products (name, price, description) VALUES (?, ?, ?)');
                $stmt->execute([$name, $price, $description]);
                $message = 'Product added successfully.';
            }
        }
    }
}

if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$_GET['edit_id']]);
    $product = $stmt->fetch();
    $mode = 'edit';
}

if (isset($_GET['view_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$_GET['view_id']]);
    $product = $stmt->fetch();
    $mode = 'view';
}

$stmt = $pdo->query('SELECT * FROM products ORDER BY id DESC');
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Installment System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="page-shell">
        <div class="card card-wide">
            <h1>Product Module</h1>
        <p class="subtitle">Add, view, edit, and delete products.</p>

        <?php if ($message !== ''): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="actions">
            <a href="dashboard.php" class="button-link secondary">Back to Dashboard</a>
        </div>

        <div class="form-grid" style="margin-top: 20px;">
            <form method="post">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id'] ?? ''); ?>">

                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>

                <label for="price">Price</label>
                <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>

                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>

                <button type="submit"><?php echo $product['id'] ? 'Update Product' : 'Add Product'; ?></button>
                <?php if ($product['id']): ?>
                    <a href="products.php" class="button-link secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <h2 style="margin-top: 30px;">Product List</h2>
        <?php if (empty($products)): ?>
            <p class="empty-state">No products found yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $productRow): ?>
                        <tr>
                            <td><?php echo (int)$productRow['id']; ?></td>
                            <td><?php echo htmlspecialchars($productRow['name']); ?></td>
                            <td><?php echo number_format((float)$productRow['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($productRow['description']); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="products.php?view_id=<?php echo (int)$productRow['id']; ?>" class="button-link secondary">View</a>
                                    <a href="products.php?edit_id=<?php echo (int)$productRow['id']; ?>" class="button-link">Edit</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$productRow['id']; ?>">
                                        <button type="submit" class="danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
