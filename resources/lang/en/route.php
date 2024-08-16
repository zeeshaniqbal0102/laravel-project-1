<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Routes Language Lines
    |--------------------------------------------------------------------------
    |
    | Here are the names for the routes
    |
    */

    "role" => [
        "list" => "role/index",
        "add" => "role/add",
        "edit" => "role/edit",
        "delete" => "role/delete"
    ],

    "resource" => [
        "list" => "resource/index",
        "add" => "resource/add",
        "edit" => "resource/edit",
        "delete" => "resource/delete"
    ],

    "user" => [
        "list" => "user/index",
        "add" => "user/add",
        "edit" => "user/edit",
        "delete" => "user/delete",
        "bulk-active" => "user/bulk-active",
        "bulk-in-active" => "user/bulk-in-active",
    ],

    "dashboard" => [
        "list" => "admin/dashboard"
    ],

    "auth" => [
        "es-panel" => "admin/login",
        "login" => "user/login",
        "logout" => "admin/logout",
        "reset" => "user/reset",
        "request" => "user/request",
        "email_password_link" => "user/email-link",
        "password_reset_form" => "user/reset-password-form",
        "password_reset_post" => "user/reset-password-post"
    ],

    "profile" => [
        "edit" => "profile/edit"
    ],

    "sendgrid" => [
        "edit" => "sendgrid/edit",
        "list" => "sendgrid/list",
        "hook" => "sendgrid/hook",
        "delete" => "sendgrid/delete",
        "add" => "sendgrid/add",
    ],

    "giftcards" => [
        "edit" => "giftcards/edit",
        "list" => "giftcards/list",
        "delete" => "giftcards/delete",
        "add" => "giftcards/add",
    ],

    "orders" => [
        "edit" => "orders/edit",
        "list" => "orders/list",
        "delete" => "orders/delete",
        "add" => "orders/add",
        "followup" => "orders/followup",
        "updatefollowup" => "orders/updatefollowup",
    ],

    "company" => [
        "edit" => "company/edit",
        "list" => "company/list",
        "delete" => "company/delete",
        "add" => "company/add",
        "summary" => "company/summary",
        "daterangemodal" => "company/daterangemodal",
        "postdaterangefrommodal" => "company/postdaterangefrommodal",
        "users" => "company/users",
        "user_delete" => "company/user_delete",
        "user_edit" => "company/user_edit",
        "invite" => "company/invite",
        "invite_modal" => "company/invite_modal",
        "processpaywithcard" => "company/processpaywithcard",
        "processpaywithcheck" => "company/processpaywithcheck",
        "sendinvoice" => "company/send",
        "setcookie" => "company/setcookie",
        "user" => "company/user",
    ],
    "discount" => [
        'add' => 'discount/add'
    ],

    "billing" => [
        "edit" => "billing/edit",
        "list" => "billing/list",
        "delete" => "billing/delete",
        "add" => "billing/add",
        "pay" => "billing/pay",
    ],

    "transactions" => [
        "list" => "transactions/list/company",
        "alllist" => "transactions/list",
    ],

    "invoice" => [
        "send" => "invoice/send",
        "showinvoices" => "invoice/showinvoices",
        "edit" => "invoice/edit",
        "delete-amount" => "invoice/delete-amount",
        "delete" => "invoice/delete",
        "mark-as-paid" => "invoice/mark-as-paid",
        "process-mark-as-paid" => "invoice/process-mark-as-paid",
        "showinvoice" => "invoice/showinvoice",
    ]

];
