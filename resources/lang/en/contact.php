<?php

return [
    'meta_title'       => 'Contact | Global Trading Ltd. Co. — Istanbul',
    'meta_description' => 'Submit an RFQ, technical enquiry, or partnership proposal. Our sourcing team responds within 24 hours. Istanbul, Türkiye.',

    'home'    => 'Home',
    'contact' => 'Contact',

    'kicker' => 'Get in touch',
    'h1_a'   => 'Contact',
    'h1_b'   => 'Global Trading.',
    'intro'  => 'Select the nature of your enquiry below. Every request is reviewed by the person responsible for that product area — not a shared inbox or automated routing system.',

    'phone_label'      => 'Direct telephone',
    'phone_hours'      => 'Istanbul, Türkiye · Monday – Friday · 09:00 – 18:00 TRT',
    'rfq_response'     => 'RFQ response',
    'within_24h'       => 'Within 24 hours',
    'languages'        => 'Languages',
    'countries_served' => 'Countries served',
    'verticals'        => 'Product verticals',

    'cat_label'   => 'My enquiry is related to',
    'cat_heading' => 'Select a category to open the right form',
    'cat' => [
        'rfq'         => ['name' => 'Request for Quotation',    'desc' => 'Products, materials, BOM, pricing, lead time, Incoterms'],
        'technical'   => ['name' => 'Technical Enquiry',        'desc' => 'Specifications, standards, approvals, documentation'],
        'partnership' => ['name' => 'Partnership & Distribution', 'desc' => 'Manufacturer representation, distribution, co-supply'],
        'media'       => ['name' => 'Press & Media',            'desc' => 'Editorial requests, interviews, company background'],
        'other'       => ['name' => 'Other',                    'desc' => 'Anything that does not fit the above categories'],
    ],

    // ── Common ────────────────────────────────────────────────
    'close'          => 'Close',
    'sending'        => 'Sending…',
    'privacy_prefix' => 'By submitting you accept our',
    'privacy'        => 'Privacy Policy',
    'required_note'  => 'Fields marked * are required.',
    'error_generic'  => 'Something went wrong. Please try again or call us on +90 216 629 20 29.',

    'f' => [
        'name' => 'Full name', 'name_ph' => 'First and last name',
        'company' => 'Company', 'company_ph' => 'Company or organisation',
        'company_name' => 'Company name', 'company_name_ph' => 'Your company',
        'work_email' => 'Work email', 'email' => 'Email address',
        'phone' => 'Phone / WhatsApp',
        'country' => 'Destination country', 'select_country' => 'Select country',
        'select' => 'Select',
    ],

    'err' => [
        'name' => 'Please enter your name.', 'company' => 'Please enter your company name.',
        'email' => 'Please enter a valid email address.', 'country_select' => 'Please select a country.',
        'country_text' => 'Please enter your country.', 'category' => 'Please select a product category.',
        'spec' => 'Please describe your requirement.', 'product' => 'Please specify the product.',
        'question' => 'Please describe your question.', 'type' => 'Please select a partnership type.',
        'proposal' => 'Please describe your proposal.', 'publication' => 'Please enter your publication.',
        'request' => 'Please describe your request.', 'subject' => 'Please enter a subject.',
        'message' => 'Please enter your message.',
    ],

    // ── RFQ ───────────────────────────────────────────────────
    'rfq' => [
        'title' => 'Request for Quotation (RFQ)',
        'sub'   => 'Provide your specification and quantity. We respond with an itemised quotation — price, lead time, Incoterm, and documentation list — within 24 hours.',
        'ok_title' => 'RFQ received — we will respond within 24 hours.',
        'ok_text'  => 'Our technical sourcing team will review your specification. For urgent requirements, call +90 216 629 20 29 directly.',
        'sec_details' => 'Your details', 'sec_product' => 'Product requirement',
        'timeline' => 'Timeline', 'category' => 'Product category', 'select_category' => 'Select category',
        'incoterm' => 'Preferred Incoterm', 'select_incoterm' => 'Select Incoterm',
        'quantity' => 'Quantity / volume', 'quantity_ph' => 'e.g. 50 units, 20 MT',
        'spec' => 'Full specification',
        'spec_ph' => 'Include: product name · grade or standard (API 6D, ASME B16.34, PE100 ISO 9080) · size range · required documentation (COA, MTC, ATEX, SIL PFD) · brand preferences or approved equivalents · destination port.',
        'spec_hint' => 'You may also email a datasheet to rfq@globaltrding.com after submitting this form.',
        'select_timeline' => 'Select timeline',
        'submit' => 'Submit RFQ — 24-hour response',
    ],

    // ── Technical ─────────────────────────────────────────────
    'technical' => [
        'title' => 'Technical Enquiry',
        'sub'   => 'Questions about specifications, standards, certification requirements, or approved equivalencies. Our technical team responds within one business day.',
        'ok_title' => 'Technical enquiry received.',
        'ok_text'  => 'Our technical team will respond with datasheets and references within one business day.',
        'product' => 'Product or category', 'product_ph' => 'e.g. Boric acid, API 6D valve, PAUT system',
        'question' => 'Your technical question',
        'question_ph' => 'Describe your technical question. Reference the applicable standard, approval, or documentation requirement where relevant.',
        'submit' => 'Send technical enquiry',
    ],

    // ── Partnership ───────────────────────────────────────────
    'partnership' => [
        'title' => 'Partnership & Distribution Enquiry',
        'sub'   => 'For manufacturers seeking regional distribution, distributors seeking Turkish-origin supply, and EPC contractors looking for a structured supply partnership. A senior team member responds within 2–3 business days.',
        'ok_title' => 'Partnership enquiry received.',
        'ok_text'  => 'A senior member of our commercial team will review your proposal and respond within 2–3 business days.',
        'job_title' => 'Job title', 'job_title_ph' => 'e.g. Export Manager',
        'country_op' => 'Country of operation', 'country_op_ph' => 'Primary country',
        'type' => 'Partnership type', 'select_type' => 'Select type',
        'proposal' => 'Describe your proposal',
        'proposal_ph' => 'Tell us about your company, the products or categories involved, markets in scope, and what you are looking for from a supply partnership with Global Trading.',
        'submit' => 'Send partnership enquiry',
    ],

    // ── Media ─────────────────────────────────────────────────
    'media' => [
        'title' => 'Press & Media Enquiry',
        'sub'   => 'Editorial requests, background information, and interview requests. We respond within one business day.',
        'ok_title' => 'Media enquiry received.',
        'ok_text'  => 'We will respond within one business day.',
        'publication' => 'Publication', 'publication_ph' => 'Publication or media outlet',
        'request' => 'Your request', 'request_ph' => 'Describe your editorial request and deadline if applicable.',
        'submit' => 'Send media enquiry',
    ],

    // ── Other ─────────────────────────────────────────────────
    'other' => [
        'title' => 'Other Enquiry',
        'sub'   => 'If your enquiry does not fit a specific category, send us a message here. We respond within one business day.',
        'ok_title' => 'Message received.',
        'ok_text'  => 'We will respond within one business day.',
        'subject' => 'Subject', 'subject_ph' => 'Brief subject of your message',
        'message' => 'Message', 'message_ph' => 'How can we help?',
        'submit' => 'Send message',
    ],

    // ── Offices / hours / languages ───────────────────────────
    'loc_label' => 'Our locations', 'loc_heading' => 'Istanbul, Türkiye',
    'hq' => 'Headquarters', 'open_maps' => 'Open in Google Maps',
    'hours_title' => 'Business hours', 'mon_fri' => 'Monday – Friday',
    'saturday' => 'Saturday', 'by_arrangement' => 'By arrangement',
    'sunday' => 'Sunday', 'closed' => 'Closed',
    'rfq_inbox' => 'RFQ inbox', 'h24' => '24h response',
    'langs_title' => 'We communicate in', 'primary' => 'Primary',
    'lang_names' => ['en' => 'English', 'tr' => 'Turkish', 'ar' => 'Arabic', 'fr' => 'French'],

    // ── Option lists (array KEY is what gets emailed to your team) ──
    'timelines' => [
        'Urgent — within 72 hours' => 'Urgent — within 72 hours',
        'Short — within 2 weeks' => 'Short — within 2 weeks',
        'Standard — 2 to 8 weeks' => 'Standard — 2 to 8 weeks',
        'Project — 2 to 6 months' => 'Project — 2 to 6 months',
        'Framework agreement — ongoing' => 'Framework agreement — ongoing',
    ],
    'incoterms' => [
        'EXW' => 'EXW', 'FOB' => 'FOB', 'CFR' => 'CFR', 'CIF' => 'CIF', 'DAP' => 'DAP', 'DDP' => 'DDP',
        'Not yet decided' => 'Not yet decided',
    ],
    'rfq_categories' => [
        'Valves & Flow Control' => 'Valves & Flow Control',
        'Instrumentation & Transmitters' => 'Instrumentation & Transmitters',
        'Actuators & Control Systems' => 'Actuators & Control Systems',
        'Rotating Equipment & Pumps' => 'Rotating Equipment & Pumps',
        'NDT & Inspection Equipment' => 'NDT & Inspection Equipment',
        'Boron Products (Eti Maden)' => 'Boron Products (Eti Maden)',
        'Industrial Chemicals' => 'Industrial Chemicals',
        'Critical Chemicals — TDI / MDI / ADN / LiPF₆ / Helium / HF' => 'Critical Chemicals — TDI / MDI / ADN / LiPF₆ / Helium / HF',
        'Polymers — PE & PP' => 'Polymers — PE & PP',
        'Lubricants & Process Chemicals' => 'Lubricants & Process Chemicals',
        'Paraffin Wax & Petroleum Jelly' => 'Paraffin Wax & Petroleum Jelly',
        'Solar Energy Systems' => 'Solar Energy Systems',
        'Security & Screening Systems' => 'Security & Screening Systems',
        'Multiple categories / project BOM' => 'Multiple categories / project BOM',
    ],
    'partner_types' => [
        'Manufacturer — seeking distribution in MENA / CIS / Africa' => 'Manufacturer — seeking distribution in MENA / CIS / Africa',
        'Distributor — seeking Turkish-origin supply' => 'Distributor — seeking Turkish-origin supply',
        'EPC contractor — seeking supply partner' => 'EPC contractor — seeking supply partner',
        'Agent or representative enquiry' => 'Agent or representative enquiry',
        'Joint venture or co-supply proposal' => 'Joint venture or co-supply proposal',
        'Other collaboration' => 'Other collaboration',
    ],
    'other_option' => 'Other',
    'country_groups' => [
        'mena' => 'Middle East & N. Africa', 'central_asia' => 'Central Asia', 'africa' => 'Africa',
        'asia' => 'South & SE Asia', 'europe' => 'Europe',
    ],
    'countries' => [
        'mena' => ['Iraq' => 'Iraq', 'UAE' => 'UAE', 'Saudi Arabia' => 'Saudi Arabia', 'Kuwait' => 'Kuwait', 'Qatar' => 'Qatar', 'Oman' => 'Oman', 'Egypt' => 'Egypt', 'Algeria' => 'Algeria', 'Libya' => 'Libya', 'Morocco' => 'Morocco'],
        'central_asia' => ['Kazakhstan' => 'Kazakhstan', 'Azerbaijan' => 'Azerbaijan', 'Uzbekistan' => 'Uzbekistan', 'Turkmenistan' => 'Turkmenistan', 'Georgia' => 'Georgia'],
        'africa' => ['Nigeria' => 'Nigeria', 'Ghana' => 'Ghana', 'Kenya' => 'Kenya', 'Tanzania' => 'Tanzania', 'Angola' => 'Angola'],
        'asia' => ['India' => 'India', 'Pakistan' => 'Pakistan', 'Bangladesh' => 'Bangladesh', 'Malaysia' => 'Malaysia', 'Indonesia' => 'Indonesia'],
        'europe' => ['Türkiye' => 'Türkiye', 'Romania' => 'Romania', 'Bulgaria' => 'Bulgaria', 'Germany' => 'Germany', 'France' => 'France', 'United Kingdom' => 'United Kingdom'],
    ],
];