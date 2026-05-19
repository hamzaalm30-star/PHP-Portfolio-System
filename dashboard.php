<?php
session_start();

// حماية لوحة التحكم
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$db_name = 'my_website_db';
$db_user = 'root';
$db_pass = '';
$msg = '';

try {
    $connect = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // مسح المعرض بالكامل تنظيف تلقائي
    if (isset($_GET['clear_all'])) {
        $stmt_all_imgs = $connect->query("SELECT image FROM projects");
        while ($row = $stmt_all_imgs->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['image'])) {
                @unlink(__DIR__ . "/uploads/" . $row['image']);
            }
        }
        $connect->query("TRUNCATE TABLE projects");
        $msg = "<div class='message success'>تم مسح المعرض القديم بالكامل بنجاح! 🧼</div>";
    }

    // حذف مشروع فردي من الجدول والمجلد
    if (isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];
        $stmt_img = $connect->prepare("SELECT image FROM projects WHERE id = :id");
        $stmt_img->execute([':id' => $delete_id]);
        $project_img = $stmt_img->fetch(PDO::FETCH_ASSOC);
        if ($project_img && !empty($project_img['image'])) {
            @unlink(__DIR__ . "/uploads/" . $project_img['image']); 
        }
        $stmt_delete = $connect->prepare("DELETE FROM projects WHERE id = :id");
        $stmt_delete->execute([':id' => $delete_id]);
        $msg = "<div class='message success'>تم حذف المشروع بنجاح! 🗑️</div>";
    }

    // إضافة مشروع جديد مع فحص الأمان المتقدم للصور الحقيقية
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_project'])) {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $link = $_POST['project_link'];
        $image_name = null; 

        if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] == 0) {
            $file_tmp = $_FILES['project_image']['tmp_name'];
            $file_orig_name = $_FILES['project_image']['name'];
            $file_ext = strtolower(pathinfo($file_orig_name, PATHINFO_EXTENSION));
            
            // 1. فحص الامتداد الخارجي المسموح
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            // 2. فحص محتوى الملف الداخلي لحظر الملفات الخبيثة المتخفية
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file_tmp);
            finfo_close($finfo);
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (in_array($file_ext, $allowed_exts) && in_array($mime_type, $allowed_mimes)) {
                $upload_path = __DIR__ . "/uploads";
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                // توليد اسم عشوائي فريد للصورة لمنع تكرار الأسماء بالخادم
                $image_name = time() . '_' . rand(100, 999) . '.' . $file_ext;
                $target_file = $upload_path . "/" . $image_name;
                move_uploaded_file($file_tmp, $target_file);
            } else {
                $msg = "<div class='message error'>تنبيه أمني: الملف المرفوع ليس صورة حقيقية مدعومة! ❌</div>";
            }
        }

        // إدخال البيانات بعد نجاح الفحص
        if (!empty($title) && !empty($desc) && empty($msg)) {
            $statement = $connect->prepare("INSERT INTO projects (title, description, image, project_link) VALUES (:title, :desc, :img, :link)");
            $statement->execute([
                ':title' => $title,
                ':desc' => $desc,
                ':img' => $image_name,
                ':link' => $link
            ]);
            $msg = "<div class='message success'>تم إضافة المشروع الجديد بأمان تام! 🚀</div>";
        }
    }

    $stmt_get = $connect->query("SELECT * FROM projects ORDER BY id DESC");
    $all_projects = $stmt_get->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $msg = "<div class='message error'>خطأ سيرفر: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم الإدارية | حماية متقدمة</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 750px; background: white; margin: 30px auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        h1, h2 { color: #333; text-align: center; }
        .user-badge { background-color: #e3faf2; color: #0ca678; padding: 8px 15px; border-radius: 20px; font-weight: bold; display: inline-block; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; text-align: right; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 8px; box-sizing: border-box; outline: none; font-family: inherit; }
        textarea { height: 100px; resize: vertical; }
        button { background-color: #667eea; color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: bold; transition: background 0.3s; }
        button:hover { background-color: #5567cc; }
        .message { padding: 12px; border-radius: 8px; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .btn-logout { background-color: #e63946; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; float: left; }
        .btn-clear-all { background-color: #d90429; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; display: block; width: fit-content; margin: 10px auto 30px auto; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; text-align: right; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; color: #555; }
        .btn-action { padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: bold; display: inline-block; margin-left: 5px; }
        .btn-delete { background-color: #fa5252; color: white; }
        .btn-edit { background-color: #228be6; color: white; }
        .td-img { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; vertical-align: middle; margin-left: 10px; }
    </style>
</head>
<body>

<div class="container">
    <a href="?logout=1" class="btn-logout">تسجيل الخروج 🚪</a>
    <div class="user-badge">المطور الحالي: <?php echo htmlspecialchars($_SESSION['user']); ?></div>
    
    <h1>لوحة الإدارة والمشاريع المصورة 📸</h1>
    
    <?php if(!empty($msg)) echo $msg; ?>

    <a href="?clear_all=1" class="btn-clear-all" onclick="return confirm('مسح كافة المشاريع القديمة وتفريغ المعرض بالكامل؟');">🧹 مسح المعرض القديم بالكامل</a>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>اسم المشروع الجديد *</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>وصف المشروع والتقنيات المستخدمة *</label>
            <textarea name="description" required></textarea>
        </div>
        <div class="form-group">
            <label>ارفع صورة للمشروع 🖼️</label>
            <input type="file" name="project_image" accept="image/*" required>
        </div>
        <div class="form-group">
            <label>رابط كود المشروع (GitHub)</label>
            <input type="text" name="project_link">
        </div>
        <button type="submit" name="add_project">نشر المشروع الجديد فوراً 🚀</button>
    </form>

    <hr style="margin: 40px 0; border: 0; border-top: 2px dashed #eee;">

    <h2>المشاريع المنشورة حالياً 📊</h2>
    <?php if (empty($all_projects)): ?>
        <p style="text-align: center; color: #999;">المعرض فارغ حالياً وجاهز لرفع مشاريعك المؤمنة.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>المشروع</th>
                    <th>التحكم</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_projects as $project): ?>
                    <tr>
                        <td>
                            <?php if(!empty($project['image'])): ?>
                                <img src="uploads/<?php echo $project['image']; ?>" class="td-img">
                            <?php endif; ?>
                            <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                        </td>
                        <td>
                            <a href="edit_project.php?id=<?php echo $project['id']; ?>" class="btn-action btn-edit">تعديل ✏️</a>
                            <a href="?delete_id=<?php echo $project['id']; ?>" class="btn-action btn-delete" onclick="return confirm('هل تريد الحذف؟');">حذف 🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
