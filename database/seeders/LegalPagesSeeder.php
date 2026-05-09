<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'disclaimer',
                'title' => ['en' => 'Disclaimer'],
                'seo' => [
                    'meta_title' => ['en' => 'Disclaimer - Global Trading'],
                    'meta_description' => ['en' => 'Legal disclaimer for Global Trading.'],
                ],
                'blocks' => [
                    [
                        'type' => 'sectionHeading',
                        'data' => [
                            'title' => 'Disclaimer',
                            'lead' => 'Information on this website is provided for general guidance and does not constitute a binding offer.',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'General information',
                            'html' => '<p>Global Trading strives to keep the information on this website accurate and up to date. However, we do not accept liability for the completeness, accuracy, or timeliness of the information provided.</p>
<p>Any reliance you place on information from this website is strictly at your own risk. We reserve the right to modify, supplement, or remove website content at any time without prior notice.</p>',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Links to third-party websites',
                            'html' => '<p>This website may contain links to external websites. We have no control over the content of external sites and accept no responsibility for them. The respective provider or operator of third-party websites is responsible for their content.</p>',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'No warranty',
                            'html' => '<p>The content is provided “as is” without warranties of any kind, either express or implied, including but not limited to warranties of merchantability, fitness for a particular purpose, and non-infringement.</p>',
                        ],
                    ],
                ],
                'is_published' => true,
                'show_in_information' => false,
                'show_in_company' => false,
                'show_in_products' => false,
                'show_in_service' => false,
            ],
            [
                'slug' => 'credits',
                'title' => ['en' => 'Credits'],
                'seo' => [
                    'meta_title' => ['en' => 'Credits - Global Trading'],
                    'meta_description' => ['en' => 'Credits and acknowledgements for Global Trading website.'],
                ],
                'blocks' => [
                    [
                        'type' => 'sectionHeading',
                        'data' => [
                            'title' => 'Credits',
                            'lead' => 'Acknowledgements and licensing information for resources used on this website.',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Website development',
                            'html' => '<p>Website design and implementation: Global Trading (internal team and approved partners).</p>',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Images & media',
                            'html' => '<p>Some images, icons, or media elements may be licensed from third-party providers. If you believe content infringes your rights, please contact us and we will investigate promptly.</p>',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Open-source software',
                            'html' => '<p>This website may use open-source components. Applicable license texts are available upon request.</p>',
                        ],
                    ],
                ],
                'is_published' => true,
                'show_in_information' => false,
                'show_in_company' => false,
                'show_in_products' => false,
                'show_in_service' => false,
            ],
            [
                'slug' => 'privacy-policy',
                'title' => ['en' => 'Privacy Policy'],
                'seo' => [
                    'meta_title' => ['en' => 'Privacy Policy - Global Trading'],
                    'meta_description' => ['en' => 'Privacy Policy describing how Global Trading processes personal data.'],
                ],
                'blocks' => [
                    [
                        'type' => 'sectionHeading',
                        'data' => [
                            'title' => 'Privacy Policy',
                            'lead' => 'This Privacy Policy explains how Global Trading collects and processes personal data when you use our website and services.',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Controller',
                            'html' => '<p>Global Trading (“we”, “us”) is responsible for processing your personal data under applicable data-protection laws.</p>',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'What data we process',
                            'html' => '<ul>
  <li>Contact details (e.g. name, email, phone, company) when you submit an inquiry or collaboration request</li>
  <li>Website usage data (e.g. device/browser information, log files) for security and performance</li>
</ul>',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Purposes and legal basis',
                            'html' => '<p>We process data to respond to your requests, improve our services, and ensure website security. Where required, we rely on consent; otherwise we rely on legitimate interests or contractual necessity.</p>',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Your rights',
                            'html' => '<p>Depending on your jurisdiction, you may have rights such as access, rectification, deletion, restriction, objection, and data portability. To exercise your rights, contact us using the details on the Contact page.</p>',
                        ],
                    ],
                ],
                'is_published' => true,
                'show_in_information' => false,
                'show_in_company' => false,
                'show_in_products' => false,
                'show_in_service' => true, // often grouped under Service / Legal
            ],
            [
                'slug' => 'data-protection-at-globaltrading',
                'title' => ['en' => 'Data Protection at Global Trading'],
                'seo' => [
                    'meta_title' => ['en' => 'Data Protection at Global Trading'],
                    'meta_description' => ['en' => 'How Global Trading approaches data protection and privacy governance.'],
                ],
                'blocks' => [
                    [
                        'type' => 'sectionHeading',
                        'data' => [
                            'title' => 'Data Protection at Global Trading',
                            'lead' => 'We take data protection seriously and continuously improve our privacy and security practices.',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Our approach',
                            'html' => '<p>We apply privacy-by-design principles, limit data to what is necessary, and enforce access controls to protect information across our systems.</p>',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Security measures',
                            'html' => '<p>We use administrative, technical, and organizational measures to protect data against unauthorized access, loss, alteration, or disclosure.</p>',
                        ],
                    ],
                ],
                'is_published' => true,
                'show_in_information' => false,
                'show_in_company' => false,
                'show_in_products' => false,
                'show_in_service' => true,
            ],
            [
                'slug' => 'responsible-disclosure-statement',
                'title' => ['en' => 'Responsible Disclosure Statement'],
                'seo' => [
                    'meta_title' => ['en' => 'Responsible Disclosure Statement - Global Trading'],
                    'meta_description' => ['en' => 'How to report security vulnerabilities to Global Trading responsibly.'],
                ],
                'blocks' => [
                    [
                        'type' => 'sectionHeading',
                        'data' => [
                            'title' => 'Responsible Disclosure Statement',
                            'lead' => 'We welcome reports of security vulnerabilities and will work with researchers to resolve issues responsibly.',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Reporting',
                            'html' => '<p>If you believe you have found a security issue, please report it with sufficient detail so we can reproduce and investigate. Do not publicly disclose the issue before we have had a reasonable opportunity to address it.</p>',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'Scope and safe harbor',
                            'html' => '<p>When conducting security research, avoid privacy violations, disruption of services, and destruction of data. We will not pursue legal action against researchers acting in good faith and within these guidelines.</p>',
                        ],
                    ],
                ],
                'is_published' => true,
                'show_in_information' => false,
                'show_in_company' => false,
                'show_in_products' => false,
                'show_in_service' => true,
            ],
            [
                'slug' => 'contact',
                'title' => ['en' => 'Contact'],
                'seo' => [
                    'meta_title' => ['en' => 'Contact - Global Trading'],
                    'meta_description' => ['en' => 'Contact Global Trading for inquiries, support, and legal requests.'],
                ],
                'blocks' => [
                    [
                        'type' => 'sectionHeading',
                        'data' => [
                            'title' => 'Contact',
                            'lead' => 'Get in touch with Global Trading. We will route your message to the appropriate department.',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'General contact',
                            'html' => '<p><strong>Email:</strong> info@globaltrding.com<br><strong>Business hours:</strong> Monday to Friday</p>',
                        ],
                    ],
                    [
                        'type' => 'richText',
                        'data' => [
                            'heading' => 'For product inquiries',
                            'html' => '<p>Please use the Inquiry form for the fastest response. Provide company details so we can process your request efficiently.</p>',
                        ],
                    ],
                ],
                'is_published' => true,
                'show_in_information' => false,
                'show_in_company' => false,
                'show_in_products' => false,
                'show_in_service' => true,
            ],
        ];

        foreach ($pages as $data) {
            Page::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'seo' => $data['seo'],
                    'blocks' => $data['blocks'],
                    'is_published' => $data['is_published'],

                    // Footer toggles:
                    'show_in_company' => $data['show_in_company'],
                    'show_in_products' => $data['show_in_products'],
                    'show_in_information' => $data['show_in_information'],
                    'show_in_service' => $data['show_in_service'],
                ],
            );
        }
    }
}