<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$db_name = 'my_website_db';
$db_user = 'root';
$db_pass = '';
$msg = '';
$project = null;

try {
    $connect = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // جلب بيانات المشروع الحالي لعرضها في الخانات
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $connect->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            die("المشروع غير موجود!");
        }
    } else {
        header("Location: dashboard.php");
        exit();
    }

    // معالجة تحديث البيانات عند ضغط زر الحفظ
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_project'])) {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $link = $_POST['project_link'];
        $image_name = $project['image']; // الحفاظ على الصورة القديمة كافتراضي

        // التحقق مما إذا كان المستخدم قد اختار صورة جديدة لتغييرها
        if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] == 0) {
            $file_tmp = $_FILES['project_image']['tmp_name'];
            $file_orig_name = $_FILES['project_image']['name'];
            $file_ext = strtolower(pathinfo($file_orig_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($file_ext, $allowed_exts)) {
                $upload_path = __DIR__ . "/uploads";
                
                // حذف الصورة القديمة من الهاتف أولاً لتوفير المساحة
                if (!empty($project['image'])) {
                    @unlink($upload_path . "/" . $project['image']);
                }

                // توليد اسم الصورة الجديدة وحفظها
                $image_name = time() . '_' . rand(100, 999) . '.' . $file_ext;
                move_uploaded_file($file_tmp, $upload_path . "/" . $image_name);
            } else {
                $msg = "<div class='message error'>امتداد الصورة غير مسموح! ❌</div>";
            }
        }

        if (!empty($title) && !empty($desc) && empty($msg)) {
            $stmt_update = $connect->prepare("UPDATE projects SET title = :title, description = :desc, image = :img, project_link = :link WHERE id = :id");
            $stmt_update->execute([
                ':title' => $title,
                ':desc' => $desc,
                ':img' => $image_name,
                ':link' => $link,
                ':id' => $id
            ]);
            
            // إعادة جلب البيانات المحدثة فوراً لعرضها
            $stmt->execute([':id' => $id]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $msg = "<div class='message success'>تم تحديث بيانات المشروع والصورة بنجاح! ✏️🚀</div>";
        }
    }

} catch (PDOException $e) {
    $msg = "<div class='message error'>خطأ: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل المشروع المصور</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 600px; background: white; margin: 40px auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        h1 { color: #333; text-align: center; font-size: 22px; }
        .form-group { margin-bottom: 15px; text-align: right; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 8px; box-sizing: border-box; outline: none; font-family: inherit; }
        textarea { height: 100px; resize: vertical; }
        button { background-color: #228be6; color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #1c7ed6; }
        .message { padding: 12px; border-radius: 8px; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .btn-back { display: inline-block; background-color: #868e96; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-bottom: 20px; }
        .current-img { width: 100px; height: 70px; object-fit: cover; border-radius: 6px; margin-top: 8px; display: block; border: 2px solid #ddd; }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn-back">إلغاء والعودة للوحة ↩️</a>
    
    <h1>تعديل وتحديث المشروع ✏️</h1>
    
    <?php if(!empty($msg)) echo $msg; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>اسم المشروع</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>
        </div>
        <div class="form-group">
            <label>الوصف والتقنيات</label>
            <textarea name="description" required><?php echo htmlspecialchars($project['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label>تغيير صورة المشروع (اختياري)</label>
            <input type="file" name="project_image" accept="image/*">
            <?php if(!empty($project['image'])): ?>
                <span style="font-size: 12px; color: #777; display:block; margin-top:10px;">الصورة الحالية المرفوعة:</span>
                <img src="uploads/<?php echo $project['image']; ?>" class="current-img">
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label>رابط الـ GitHub</label>
            <input type="text" name="project_link" value="<?php echo htmlspecialchars($project['project_link']); ?>">
        </div>
        <button type="submit" name="update_project">حفظ التعديلات الجديدة 💾</button>
    </form>
</div>

</body>
</html>
