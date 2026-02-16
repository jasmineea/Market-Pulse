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

    /*
    |--------------------------------------------------------------------------
    | AI Lab Collaboration Form Options
    |--------------------------------------------------------------------------
    */

    'ai_lab_collaboration_types' => [
        'research_collaboration' => 'Research collaboration',
        'pilot_partner' => 'Pilot partner',
        'faculty_sponsorship_mii' => 'Faculty sponsorship / MII alignment',
        'data_sharing' => 'Data sharing agreement',
        'methodology_review' => 'Methodology review / validation',
        'student_project' => 'Student project / capstone',
        'policy_consultation' => 'Policy consultation',
        'other' => 'Other',
    ],

    'ai_lab_areas_of_interest' => [
        'forecasting' => 'Forecast Lab',
        'fairness' => 'Fairness Audit',
        'stress_testing' => 'Stress Testing',
        'equity_metrics' => 'Equity & Access Metrics',
        'transparency' => 'Data & Method Transparency',
        'governance' => 'AI governance standards',
        'bias_remediation' => 'Bias detection & remediation',
        'other' => 'Other',
    ],

    'ai_lab_timelines' => [
        'immediate' => 'Within 1–3 months',
        'quarter' => 'Within this quarter',
        'semester' => 'Within this semester',
        'year' => 'Within this year',
        'exploratory' => 'Exploratory / no fixed timeline',
    ],

];
