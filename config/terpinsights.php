<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Starter Plan Limits
    |--------------------------------------------------------------------------
    |
    | Rolling window size: current month + (N-1) prior months. Starter users
    | can only access data within this window.
    |
    */

    'starter_allowed_months' => (int) env('STARTER_ALLOWED_MONTHS', 3),

    'starter_history_limit' => (int) env('STARTER_HISTORY_LIMIT', 3),

    /*
    |--------------------------------------------------------------------------
    | Persona & Operator Type (Waitlist + Outreach CRM)
    |--------------------------------------------------------------------------
    |
    | Primary Persona (persona_type) and conditional Business Type (operator_type
    | when persona = operator). Shared by waitlist and outreach for consistent
    | segmentation and admin filtering.
    |
    */

    'persona_types' => [
        'operator' => 'Operator',
        'policy_government' => 'Policy / Government',
        'journalist_media' => 'Journalist / Media',
        'researcher_analyst' => 'Researcher / Analyst',
        'investor' => 'Investor',
        'cannabis_tech_founder' => 'Cannabis Tech Founder',
        'advocate_nonprofit' => 'Advocate / Nonprofit',
        'other' => 'Other',
    ],

    'operator_types' => [
        'dispensary' => 'Dispensary',
        'cultivator_grower' => 'Cultivator / Grower',
        'processor_manufacturer' => 'Processor / Manufacturer',
        'brand_marketing_lead' => 'Brand / Marketing Lead',
        'ancillary_service_provider' => 'Ancillary Service Provider',
    ],

];
