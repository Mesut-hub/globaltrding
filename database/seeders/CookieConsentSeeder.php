<?php

namespace Database\Seeders;

use App\Models\CookieCategory;
use App\Models\CookieSetting;
use Illuminate\Database\Seeder;

class CookieConsentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'key'         => 'necessary',
                'label'       => ['en' => 'Strictly Necessary', 'tr' => 'Zorunlu', 'ar' => 'ضروري', 'fr' => 'Strictement nécessaire'],
                'description' => ['en' => 'These cookies are required for the website to function and cannot be switched off. They are usually only set in response to actions made by you such as setting your privacy preferences, logging in or filling in forms.', 'tr' => 'Bu çerezler web sitesinin çalışması için gereklidir ve devre dışı bırakılamaz.', 'ar' => 'هذه الكوكيز ضرورية لعمل الموقع ولا يمكن إيقاف تشغيلها.', 'fr' => 'Ces cookies sont nécessaires au fonctionnement du site Web et ne peuvent pas être désactivés.'],
                'is_required' => true,
                'is_enabled'  => true,
                'sort_order'  => 10,
            ],
            [
                'key'         => 'analytics',
                'label'       => ['en' => 'Performance & Analytics', 'tr' => 'Performans ve Analitik', 'ar' => 'الأداء والتحليلات', 'fr' => 'Performance et analytique'],
                'description' => ['en' => 'These cookies allow us to count visits and traffic sources so we can measure and improve the performance of our site. They help us understand which pages are the most and least popular and see how visitors move around the site. All information these cookies collect is aggregated and therefore anonymous.', 'tr' => 'Bu çerezler ziyaretleri sayarak web sitemizin performansını ölçmemize yardımcı olur.', 'ar' => 'تتيح لنا هذه الكوكيز قياس وتحسين أداء موقعنا.', 'fr' => 'Ces cookies nous permettent de mesurer et améliorer les performances de notre site.'],
                'is_required' => false,
                'is_enabled'  => true,
                'sort_order'  => 20,
            ],
            [
                'key'         => 'marketing',
                'label'       => ['en' => 'Marketing & Targeting', 'tr' => 'Pazarlama ve Hedefleme', 'ar' => 'التسويق والاستهداف', 'fr' => 'Marketing et ciblage'],
                'description' => ['en' => 'These cookies may be set through our site by our advertising partners. They may be used by those companies to build a profile of your interests and show you relevant adverts on other sites. They do not store directly personal information, but are based on uniquely identifying your browser and internet device.', 'tr' => 'Bu çerezler, reklam ortaklarımız tarafından ilgi profilinizi oluşturmak için kullanılır.', 'ar' => 'يتم تعيين هذه الكوكيز من قبل شركاء الإعلان لبناء ملف تعريف اهتماماتك.', 'fr' => 'Ces cookies peuvent être définis via notre site par nos partenaires publicitaires.'],
                'is_required' => false,
                'is_enabled'  => true,
                'sort_order'  => 30,
            ],
            [
                'key'         => 'social',
                'label'       => ['en' => 'Social Media', 'tr' => 'Sosyal Medya', 'ar' => 'وسائل التواصل الاجتماعي', 'fr' => 'Réseaux sociaux'],
                'description' => ['en' => 'These cookies are set by social media services that we have added to the site to enable you to share our content with your friends and networks. They are capable of tracking your browser across other sites. This may impact the content and messages you see on other websites you visit.', 'tr' => 'Bu çerezler, içerikleri paylaşmanıza olanak tanıyan sosyal medya hizmetleri tarafından ayarlanır.', 'ar' => 'يتم تعيين هذه الكوكيز بواسطة خدمات التواصل الاجتماعي.', 'fr' => 'Ces cookies sont définis par des services de réseaux sociaux que nous avons ajoutés au site.'],
                'is_required' => false,
                'is_enabled'  => true,
                'sort_order'  => 40,
            ],
        ];

        foreach ($categories as $cat) {
            CookieCategory::query()->updateOrCreate(['key' => $cat['key']], $cat);
        }

        $settings = [
            ['key' => 'banner_title',       'value' => ['en' => 'We value your privacy', 'tr' => 'Gizliliğinize değer veriyoruz', 'ar' => 'نحن نقدر خصوصيتك', 'fr' => 'Nous respectons votre vie privée']],
            ['key' => 'banner_description', 'value' => ['en' => 'We use cookies to enhance your browsing experience, serve personalised content, and analyse our traffic. By clicking "Accept All", you consent to our use of cookies. You can manage your preferences at any time.', 'tr' => 'Gezinme deneyiminizi geliştirmek, kişiselleştirilmiş içerik sunmak ve trafiğimizi analiz etmek için çerezler kullanıyoruz.', 'ar' => 'نستخدم ملفات تعريف الارتباط لتحسين تجربة التصفح وتقديم محتوى مخصص وتحليل حركة المرور.', 'fr' => 'Nous utilisons des cookies pour améliorer votre expérience de navigation, proposer du contenu personnalisé et analyser notre trafic.']],
            ['key' => 'consent_version',    'value' => '1.0'],
            ['key' => 'policy_url_suffix',  'value' => 'pages/privacy-policy'],
            ['key' => 'show_reject_all',    'value' => true],
            ['key' => 'show_manage',        'value' => true],
            ['key' => 'position',           'value' => 'bottom'], // bottom | bottom-left | bottom-right
        ];

        foreach ($settings as $s) {
            CookieSetting::query()->updateOrCreate(['key' => $s['key']], ['value' => $s['value']]);
        }
    }
}