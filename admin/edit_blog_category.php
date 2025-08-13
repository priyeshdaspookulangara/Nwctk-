<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit Blog Category";
$msg = "";
$error_msg = "";
$category_id = null;
$category = null;

// Get category ID from URL
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $category_id = $_GET['id'];
} else {
    header("Location: manage_blog_categories.php?error=Invalid Category ID");
    exit;
}

// Fetch existing category data
$sql_fetch = "SELECT id, name FROM blog_categories WHERE id = ?";
if ($stmt_fetch = mysqli_prepare($conn, $sql_fetch)) {
    mysqli_stmt_bind_param($stmt_fetch, "i", $category_id);
    mysqli_stmt_execute($stmt_fetch);
    $result_fetch = mysqli_stmt_get_result($stmt_fetch);
    if ($result_fetch && mysqli_num_rows($result_fetch) == 1) {
        $category = mysqli_fetch_assoc($result_fetch);
    } else {
        header("Location: manage_blog_categories.php?error=Category not found");
        exit;
    }
    mysqli_stmt_close($stmt_fetch);
} else {
    die("Error preparing fetch statement: " . mysqli_error($conn));
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    if ($posted_category_id !== $category_id) {
        $error_msg = "Error: Category ID mismatch.";
    } else {
        $name = trim($_POST['name']);

        if (empty($name)) {
            $error_msg = "Category name is required.";
        }

        if (empty($error_msg)) {
            $sql_update = "UPDATE blog_categories SET name = ? WHERE id = ?";
            if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                mysqli_stmt_bind_param($stmt_update, "si", $name, $category_id);
                if (mysqli_stmt_execute($stmt_update)) {
                    $msg = "Category updated successfully!";
                    // Re-fetch data to display updated name
                    $category['name'] = $name;
                } else {
                    $error_msg = "Error updating category: " . mysqli_stmt_error($stmt_update);
                }
                mysqli_stmt_close($stmt_update);
            } else {
                $error_msg = "Error preparing update statement: " . mysqli_error($conn);
            }
        }
    }
}

?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?php echo $page_title; ?>: <?php echo htmlspecialchars($category['name']); ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item"><a href="manage_blog_categories.php">Blog Categories</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Update Category Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $category_id; ?>" method="post">
                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">

                    <div class="form-group">
                        <label for="name">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Category</button>
                    <a href="manage_blog_categories.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
