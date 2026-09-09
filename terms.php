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
    <link rel="stylesheet" href="<?php echo $assetsPath; ?>/css/terms.css">
</head>
<body>
   
     <!-- ===== HEADER ===== -->
 <?php require_once 'includes/header.php'; ?>
     <section class="page-hero">
        <div class="container">
            <h1>اتفاقية الاستخدام</h1>
            <p>آخر تحديث: 24 مايو 2024</p>
        </section>

        <div class="terms-content">
            <p>مرحبًا بك في PSPKG-arabic. يرجى قراءة هذه الاتفاقية بعناية قبل استخدام موقعنا.</p>

            <h2>1. قبول الاتفاقية</h2>
            <p>باستخدامك لموقع PSPKG-arabic، فإنك توافق على الالتزام بشروط هذه الاتفاقية. إذا كنت لا توافق على هذه الشروط، يرجى عدم استخدام الموقع.</p>

            <h2>2. استخدام الموقع</h2>
            <p>يمنع استخدام الموقع لأي أغراض غير قانونية أو محظورة. أنت تتعهد بعدم:</p>
            <ul>
                <li>انتهاك أي قوانين محلية أو دولية قابلة للتطبيق.</li>
                <li>انتهاك حقوق الملكية الفكرية للآخرين.</li>
                <li>نشر أي محتوى مسيء أو تشهيري أو فاحش.</li>
                <li>محاولة الوصول إلى أجزاء من الموقع التي لم يتم تفويضها لك الوصول إليها.</li>
            </ul>

            <h2>3. الملكية الفكرية</h2>
            <p>جميع المحتويات الموجودة على PSPKG-arabic، بما في ذلك النصوص والصور والرسومات والشعارات والأيقونات والصور والصوتيات ومقاطع الفيديو، هي ملكية PSPKG-arabic أو مرخصيها ومحمية بموجب قوانين حقوق النشر والعلامات التجارية وحقوق الملكية الفكرية الأخرى.</p>

            <h2>4. إخلاء المسؤولية</h2>
            <p>يتم تقديم المعلومات على هذا الموقع "كما هي" دون أي ضمانات من أي نوع، سواء كانت صريحة أو ضمنية. نحن نلغي جميع الضمانات والتعبيرات بشأن دقة المعلومات أو ملاءمتها لغرض معين.</p>

            <h2>5. التعديلات على الاتفاقية</h2>
            <p>نحتفظ بالحق في تعديل هذه الاتفاقية من وقت لآخر دون إشعار مسبق. استخدامك المستمر للموقع بعد أي تغييرات يعني قبولك للشروط المعدلة.</p>

            <h2>6. القانون الحاكم</h2>
            <p>تخضع هذه الاتفاقية وتفسر بموجب قوانين [اسم دولتك/المنطقة] دون النظر إلى تعارض أحكام القانون.</p>
        </div>
    </main>
    <!-- ===== FOOTER ===== -->  <?php require_once 'includes/footer.php'; ?>
    
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
