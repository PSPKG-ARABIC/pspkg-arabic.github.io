<?php
require_once 'config.php';
// تحديد المتغيرات الأساسية
 $pageTitle = "سياسة الخصوصية   - PSGOLD4GAMER";
 $pageDescription = "تواصل مع فريق PSGOLD4GAMER للحصول على الدعم أو طرح أي استفسارات حول ألعاب بلايستيشن.";
 $canonicalUrl = "https://PSGOLD4GAMER.com/contact.php";

// تعريف التصنيفات مرة واحدة فقط
 $categories = [
    'all' => 'الكل',
    'action' => 'أكشن',
    'rpg' => 'ألعاب تقمص أدوار',
    'racing' => 'سباقات',
    'sports' => 'رياضة',
    'adventure' => 'مغامرات',
    'horror' => 'رعب',
    'simulation' => 'محاكاة',
    'souls' => 'ألعاب Souls',
    'arabic' => 'ألعاب معربة',
    'hack-and-slash' => 'Hack and Slash',
    'survival' => 'بقاء',
    'stealth' => 'تسلل',
    'puzzle' => 'ألغاز',
    'shooter' => 'تصويب',
    'fighting' => 'قتال',
    'metroidvania' => 'Metroidvania'
];

// الحصول على معرف التصنيف من URL
 $currentCategory = isset($_GET['category']) ? $_GET['category'] : 'action';
 $activeCategory = $currentCategory;

// تحديد لغة الصفحة
 $lang = isset($_GET['lang']) ? $_GET['lang'] : 'ar';

// تحديد مسار ملفات CSS و JS
 $assetsPath = 'assets';

// دالة لقراءة ملف JSON وإرجاع مصفوفة PHP
function load_json_data($filename) {
    $filepath = __DIR__ . '/data/' . $filename;
    if (!file_exists($filepath)) {
        error_log("JSON file not found: " . $filepath);
        return [];
    }
    $json_data = file_get_contents($filepath);
    return json_decode($json_data, true);
}

// جلب بيانات الألعاب
 $ps4_games = load_json_data('ps4-games.json');
 $ps5_games = load_json_data('ps5-games.json');
 $ps3_games = load_json_data('ps3-games.json');
 $all_games = array_merge($ps4_games, $ps5_games, $ps3_games);

// معالجة نموذج الاتصال
 $formMessage = '';
 $formMessageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $formMessage = 'يرجى ملء جميع الحقول المطلوبة.';
        $formMessageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formMessage = 'يرجى إدخال بريد إلكتروني صحيح.';
        $formMessageType = 'error';
    } else {
        $formMessage = 'تم إرسال رسالتك بنجاح. سنتواصل معك قريباً.';
        $formMessageType = 'success';
        $name = $email = $subject = $message = '';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" >
    <title>PSPKG-arabic - تحميل ألعاب PS4, PS5, PS3 مجاناً | العاب بلايستيشن بالعربية</title>
    <meta name="description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta name="keywords" content="ألعاب PS4, ألعاب PS5, ألعاب PS3, تحميل ألعاب بلايستيشن, العاب معربة, PKG, PSPKG">
    <link rel="canonical" href="https://pspkg-arabic.com/">
    
    <!-- Open Graph Tags للسوشيال ميديا -->
    <meta property="og:title" content="PSPKG-arabic - تحميل ألعاب PS4, PS5, PS3 مجاناً">
    <meta property="og:description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta property="og:image" content="https://pspkg-arabic.com/images/og-image.jpg">
    <meta property="og:url" content="https://pspkg-arabic.com/">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_AR">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="PSPKG-arabic - تحميل ألعاب PS4, PS5, PS3 مجاناً">
    <meta name="twitter:description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta name="twitter:image" content="https://pspkg-arabic.com/images/twitter-image.jpg">
    
    <!-- باقي العلامات الحالية -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="<?php echo $assetsPath; ?>/css/privacy.css">
</head>
<body>
     <!-- ===== HEADER ===== -->
 <?php require_once 'includes/header.php'; ?>
    <section class="page-hero">
        <div class="container">
            <h1>سياسة الخصوصية</h1>
            <p>خصوصيتك مهمة بالنسبة لنا. توضح هذه السياسة كيفية جمعنا واستخدامنا وحماية معلوماتك.</p>
        </div>
    </section>

 <!-- ===== MAIN CONTENT ===== -->
      <main class="container">
        <div class="privacy-content">
            <h2>1. المعلومات التي نجمعها</h2>
            <p>قد نطلب منك تزويدنا بمعلومات شخصية قابلة للتعريف عند استخدامك لموقعنا، بما في ذلك على سبيل المثال لا الحصر:</p>
            <ul>
                <li>عنوان البريد الإلكتروني</li>
                <li>الاسم والمعلومات الشخصية الأخرى</li>
                <li>معلومات الاتصال</li>
            </ul>

            <h2>2. كيف نستخدم معلوماتك</h2>
            <p>نحن نستخدم المعلومات التي نجمعها لأغراض مختلفة، بما في ذلك:</p>
            <ul>
                <li>تقديم وصيانة خدمتنا.</li>
                <li>إعلامك بالتغييرات التي تطرأ على خدمتنا.</li>
                <li>تزويدك بمحتوى مخصص ومفيد.</li>
                <li>مراقبة استخدام خدمتنا.</li>
                <li>الكشف عن ومنع الاحتيال.</li>
            </ul>

            <h2>3. ملفات تعريف الارتباط (Cookies)</h2>
            <p>نحن نستخدم ملفات تعريف الارتباط و/أو تقنيات تتبع أخرى لتحليل سلوك المستخدم على موقعنا وتقديم محتوى مخصص. يمكنك اختيار عدم قبول ملفات تعريف الارتباط من خلال إعدادات متصفحك.</p>

            <h2>4. أمان البيانات</h2>
            <p>نحن ملتزمون بحماية بياناتك. نحن نستخدم بروتوكولات أمنية مناسبة لحماية المعلومات التي نجمعها من الوصول أو التغيير أو الكشف أو التدمير غير المصرح به.</p>

            <h2>5. روابط إلى مواقع أخرى</h2>
            <p>قد يحتوي موقعنا على روابط إلى مواقع أخرى لا نملكها أو نتحكم فيها. نحن لسنا مسؤولين عن محتوى أو ممارسات الخصوصية في مواقع الطرف الثالث.</p>

            <h2>6. تغييرات سياسة الخصوصية</h2>
            <p>قد نقوم بتحديث سياسة الخصوصية الخاصة بنا من وقت لآخر. سنقوم بإعلامك بأي تغييرات عن طريق نشر السياسة الجديدة على هذه الصفحة.</p>
        </div>
    </main>
    
           <?php require_once 'includes/footer.php'; ?>

    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "PSPKG-arabic",
        "url": "https://pspkg-arabic.com",
        "logo": "https://pspkg-arabic.com/assets/images/logo.png",
        "description": "أفضل موقع لتحميل العاب بلايستيشن PS4, PS5, PS3 مجاناً بالعربي والإنجليزية",
        "sameAs": [
            "https://www.youtube.com/PKGStation",
            "https://www.facebook.com/PKGStation",
            "https://t.me/PSPKG-arabic"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "availableLanguage": "Arabic"
        }
    }
    </script>

    <!-- بيانات الموقع (Website) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "PSPKG-arabic",
        "url": "https://pspkg-arabic.com",
        "description": "أفضل موقع لتحميل العاب بلايستيشن PS4, PS5, PS3 مجاناً بالعربي والإنجليزية",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://pspkg-arabic.com/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    
    <!-- Inline Script to pass PHP data to JavaScript -->
    <script>
        window.phpVars = { lang: '<?php echo $lang; ?>', assetsPath: '<?php echo $assetsPath; ?>' };
        window.allGamesData = <?php echo json_encode($all_games); ?>;
    </script>
    
    <!-- External JavaScript -->
    <script src="<?php echo $assetsPath; ?>/js/about.js"></script>
</body>
</html>