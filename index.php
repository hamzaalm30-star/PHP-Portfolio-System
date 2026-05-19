<?php
// الاتصال بقاعدة البيانات لجلب المشاريع وعرضها بالصور
$host = 'localhost';
$db_name = 'my_website_db';
$db_user = 'root';
$db_pass = '';
$projects = [];

try {
    $connect = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // جلب جميع المشاريع من الأحدث إلى الأقدم
    $statement = $connect->query("SELECT * FROM projects ORDER BY id DESC");
    $projects = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "خطأ في السيرفر: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معرض أعمال المطور المحترف | بالصور</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; padding: 50px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        header h1 { margin: 0; font-size: 2.5rem; }
        header p { margin: 10px 0 0 0; font-size: 1.1rem; opacity: 0.9; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .projects-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-top: 30px; }
        
        /* كارت المشروع الاحترافي المطور */
        .project-card { background: white; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid #eef2f5; transition: transform 0.3s, box-shadow 0.3s; display: flex; flex-direction: column; }
        .project-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.08); }
        
        /* تنسيق صورة المشروع */
        .project-img-wrapper { width: 100%; height: 180px; overflow: hidden; background-color: #eee; }
        .project-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
        .project-card:hover .project-img { transform: scale(1.05); }
        
        /* محتوى الكارت بالداخل */
        .project-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .project-card h3 { margin: 0 0 12px 0; color: #2d3748; font-size: 1.3rem; text-align: left; dir: ltr; }
        .project-card p { color: #627d98; font-size: 0.95rem; line-height: 1.6; text-align: left; margin-bottom: 20px; font-family: 'Courier New', Courier, monospace; dir: ltr; flex-grow: 1; }
        
        .project-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
        .project-link { display: inline-block; background-color: #eef2f5; color: #4a5568; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 0.85rem; transition: background 0.2s; }
        .project-link:hover { background-color: #667eea; color: white; }
        .admin-btn { display: block; width: fit-content; margin: 20px auto; background-color: #2d3748; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; }
    </style>
</head>
<body>

<header>
    <h1>بوابة المطور الاحترافية 💻</h1>
    <p>مرحباً بكم في معرض أعمالي التقني المتكامل - مبرمج ومطور ويب متكامل</p>
</header>

<div class="container">
    <a href="dashboard.php" class="admin-btn">👑 الدخول للوحة التحكم الإدارية</a>
    
    <h2 style="text-align: center; color: #4a5568;">المشاريع البرمجية الحالية</h2>
    <hr style="width: 80px; border: 2px solid #667eea; border-radius: 2px; margin-bottom: 30px;">

    <div class="projects-grid">
        <?php if (empty($projects)): ?>
            <p style="text-align: center; color: #999; grid-column: 1/-1;">لم يتم نشر أي مشاريع في المعرض بعد. ارجع للوحة التحكم وأضف مشروعك الأول!</p>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    
                    <?php if (!empty($project['image'])): ?>
                        <div class="project-img-wrapper">
                            <img src="uploads/<?php echo $project['image']; ?>" class="project-img" alt="Project Image">
                        </div>
                    <?php endif; ?>
                    
                    <div class="project-content">
                        <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                        
                        <div class="project-footer">
                            <?php if (!empty($project['project_link'])): ?>
                                <a href="<?php echo htmlspecialchars($project['project_link']); ?>" target="_blank" class="project-link">View Code/GitHub 🔗</a>
                            <?php else: ?>
                                <span class="project-link" style="opacity: 0.6; cursor: not-allowed;">Private Repository 🔒</span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
