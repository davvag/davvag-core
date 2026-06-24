# DAVVAG Apps Feature Summary

Generated from `davvag-core/localhost/apps` on 2026-06-23.

This is the summarized companion to `docs/apps_features_list.md`. It keeps every top-level app in scope, but removes the long route, dependency, and PHP method listings.

## Snapshot

- App folders scanned: 75
- Standard `app.json` descriptors: 74
- Alternate descriptor: `davvag-stripe-1/app..json`
- Missing descriptors: none
- Declared services: 101
- Registered service-handler components: 107
- Public PHP service methods: 535

## Main User Groups

- System administrators: platform setup, app management, settings, schemas, users, tax, UOM, currency, hosting, and dock tools.
- Content editors/site admins: CMS pages, articles, albums, menus, cards, buttons, themes, and site content.
- Store admins/sellers: product, inventory, order, store, marketplace, and seller workflows.
- Customers/shoppers: marketplace, checkout, cart, order, and payment flows.
- Finance/payment admins: banking, deposits, invoices, receipts, IPG, Stripe, DirectPay, and payment settings.
- Registered users: login, profile, password, social login, and account pages.
- Riders/delivery staff: delivery and rider order flows.
- Students/teachers: Bible/study/course apps, timetables, attendance, gradebook, assignments, and enrollments.
- Developers/automation admins: AI agents, flow designer, ETL, task automation, and workflow tooling.
- Marketing/support operators: Facebook Messenger, broadcasts, email configuration, and task email tools.

## Functional Areas

| Area | Main apps | Summary |
| --- | --- | --- |
| AI and workflow automation | `ai-agent-creator`, `ai-chatgpt-agent`, `davvag-agent-flow`, `davvag-flow-designer`, `davvag-etl-system`, `ephraim` | Agent configuration, ChatGPT agent runtime, flow execution/design, ETL/sample automation, and project workflow tools. |
| CMS and content | `davvag-cms`, `davvag-cms-davvag`, `davvag-cms-v7`, `davvag-cms-v7-setting`, `davvag-cms-generalapps`, `davvag-cms-generalapps-v2`, `davvag-artical-app`, `davvag-album`, `dock`, `dock-settings` | Site shell, content pages, article/album/category tools, navigation, theme/settings, uploads, routing, and dock configuration. |
| Commerce and orders | `davvag-shop`, `davvag-shop-v2`, `stelup_shop`, `raha`, `romashop`, `com_qti_students`, `productapp`, `productapp-v2`, `productcategories`, `product_marketplace`, `inventory`, `stores`, `order`, `pending-orders`, `davvag-orders`, `rider-orders`, `rider-pending`, `riders` | Storefronts, products, marketplaces, inventory, carts, checkout, seller operations, pending orders, riders, and delivery. |
| Payments and finance | `davvag-banking`, `davvag-banking-settings`, `davvag-directpay-lk`, `davvag-directpay-lk-settings`, `davvag-ipg`, `davvag-stripe`, `davvag-stripe-1`, `profileapp`, `profileapp-admin`, `profileapp.v1`, `grn` | Payment gateways, banking settings, charge forms, deposits, invoices, receipts, transactions, collections, and GRN flows. |
| Users and identity | `userapp`, `userapp_social`, `davvag-useradmin`, `profile-catogory-creator`, `profileapp`, `profileapp-admin`, `profileapp.v1` | Login, signup, reset password, profiles, user groups, profile categories, and account management. |
| Education and reference | `course-manager`, `lbc-study-app`, `qib-reg-app`, `bible`, `bible-copy`, `uom`, `i18n`, `tax-master`, `currency-configuration` | Courses, timetables, attendance, assignments, gradebook, study/profile search, Bible reader/reference data, UOM, i18n, tax, and currency. |
| Messaging and support | `facebook-messanger`, `facebook-messanger-v1`, `facebooktest`, `davvag-email-settings`, `task-tracker` | Facebook Messenger integration, keyword/broadcast tools, email configuration, and task email client support. |
| Platform/admin tooling | `davvag-app-manager`, `davvag-attributes`, `davvag-global`, `davvag-hosting-console`, `davvag-tools`, `davvag-sample-app-1`, `davvag-sip`, `uicomponents`, `ar-app` | App launchers/permissions, schemas, dynamic attributes, hosting/backup console, upload/download/image utilities, UI components, and AR/media utilities. |
| Tasks and scheduling | `davvag-task`, `davvag-scheduler`, `task-tracker` | Task/project management, schedules, time tracking, password vault, email tasks, and dashboards. |

## Compact App List

| App | Primary feature | Main users |
| --- | --- | --- |
| `ai-agent-creator` | AI agent config generation, saving, testing, running, reusable app interactions, providers, and sessions. | System administrators, developers/automation admins |
| `ai-chatgpt-agent` | ChatGPT agent configuration and chat service. | System administrators, developers/automation admins |
| `ar-app` | AR camera/object viewer, image capture, and reference/UOM support. | System administrators, media/content users |
| `bible` | Bible app with UOM-style list/form management. | Students/learners, teachers/course admins, system administrators |
| `bible-copy` | Bible reader variant. | Students/learners, teachers/course admins |
| `com_qti_students` | Student marketplace, products, checkout, profile view, and donation flow. | Students/learners, teachers/course admins, shoppers, store admins |
| `course-manager` | Courses, timetable, weekly timetable, attendance, gradebook, assignments, profiles, and dashboard. | Students/learners, teachers/course admins, administrators |
| `currency-configuration` | Currency configuration administration. | System administrators |
| `davvag-agent-flow` | Agent flow runtime/API and flow console. | System administrators, developers/automation admins |
| `davvag-album` | Album, category, and CMS album management. | Content editors/site admins, media/content users |
| `davvag-app-manager` | App permissions, launchers, schemas, and app manager tooling. | System administrators |
| `davvag-artical-app` | Article creation and article list management. | Content editors/site admins |
| `davvag-attributes` | Attribute creator, app handler, and attribute generation tooling. | System administrators, developers |
| `davvag-banking` | Banking/payment charge workflows. | Finance/payment admins |
| `davvag-banking-settings` | Bank settings, bank lists, deposits, and payment setup. | System administrators, finance/payment admins |
| `davvag-cms` | Legacy CMS shell with routing, uploads, dynamic attributes, products, user/profile, and content pages. | Content editors, system admins, registered users |
| `davvag-cms-davvag` | Davvag CMS/site variant with domain info, products, auth, and uploads. | Content editors, shoppers, admins |
| `davvag-cms-generalapps` | CMS general content apps: articles, buttons, cards, categories, carousels, albums, menus, and settings. | Content editors/site admins |
| `davvag-cms-generalapps-v2` | V2 CMS general content tools. | Content editors/site admins |
| `davvag-cms-v7` | CMS v7 shell, pages, nav/footer, themes, routes, uploads, and auth/session utilities. | Content editors/site admins, system administrators |
| `davvag-cms-v7-setting` | CMS v7 settings console/API. | Content editors/site admins, system administrators |
| `davvag-directpay-lk` | DirectPay payment charge integration. | Finance/payment admins |
| `davvag-directpay-lk-settings` | DirectPay configuration/settings. | System administrators, finance/payment admins |
| `davvag-email-settings` | Email configuration and test form. | System administrators, support operators |
| `davvag-etl-system` | Sample ETL/input/popup app handler. | Developers/automation admins |
| `davvag-flow-designer` | Workflow designer and flow-designer API. | Developers/automation admins |
| `davvag-global` | Global sample input/API app. | System administrators |
| `davvag-hosting-console` | Hosting console, backup app/files, sample popup, and test form. | System administrators |
| `davvag-ipg` | IPG selector and test payment form. | Finance/payment admins |
| `davvag-orders` | Order taking, pending orders, pending bids. | Store admins/sellers |
| `davvag-sample-app-1` | Sample app with input, popup, and app handler. | App users, admins |
| `davvag-scheduler` | Schedule management. | App users, admins |
| `davvag-shop` | Marketplace products, cart/checkout, and orders. | Shoppers, store admins/sellers |
| `davvag-shop-v2` | Marketplace V2 with product, order, checkout, and bid confirmation flows. | Shoppers, store admins/sellers |
| `davvag-sip` | Sample SIP/test form app. | App users, admins |
| `davvag-stripe` | Stripe payment gateway charge form. | Finance/payment admins |
| `davvag-stripe-1` | Stripe IPG charge/order flow. | Finance/payment admins |
| `davvag-task` | Tasks, projects, project lists, object viewing, and project types. | App users, admins |
| `davvag-tools` | App downloader, file uploader, image cropper, capture, and object viewer. | System administrators |
| `davvag-useradmin` | User and user group administration. | System administrators |
| `dock` | Webdock shell with routes, uploads, dynamic attributes, products, profile/user, and CMS pieces. | System administrators, content editors, registered users |
| `dock-settings` | Web dock settings. | System administrators, content editors |
| `ephraim` | Project/course sample app with popup/test form. | Developers/automation admins |
| `facebook-messanger` | Facebook Messenger platform integration. | Marketing/support operators |
| `facebook-messanger-v1` | Messenger keywords, broadcasts, terms/privacy pages, and send-message tools. | Marketing/support operators |
| `facebooktest` | UOM/reference-data test app. | System administrators |
| `grn` | GRN all/form workflows. | App users, admins |
| `i18n` | Internationalization management. | App users, admins |
| `inventory` | Inventory handling. | Store admins/sellers |
| `lbc-dashborad-report` | LBC reports, profiles, dashboard income/enrollment, and customers. | Registered users, media/content users |
| `lbc-study-app` | Profile search, course/study functions, and settings. | Students/learners, teachers/course admins, admins |
| `order` | Order and inventory handlers. | Store admins/sellers |
| `pending-orders` | Pending order and cross-domain order flow. | Store admins/sellers, finance admins |
| `product_marketplace` | Product marketplace. | Shoppers, store admins/sellers |
| `productapp` | Product management. | Store admins/sellers |
| `productapp-v2` | Product management V2. | Store admins/sellers |
| `productcategories` | Product category list/form. | Store admins/sellers |
| `profile-catogory-creator` | Profile category management. | System administrators, registered users |
| `profileapp` | Profiles, invoices, receipts, deposits, PO, and profile settings. | Registered users, finance admins |
| `profileapp-admin` | Profile admin and invoice deletion/test tools. | System administrators, finance admins |
| `profileapp.v1` | Profile V1: profiles, invoices, receipts, deposits, collections, PO, GRN, and transactions. | Registered users, finance admins |
| `qib-reg-app` | QIB enrollment/result upload/result workflows. | App users, admins |
| `raha` | Raha storefront/domain app with products, cart, orders, riders, store, inventory, login, and payment pages. | Shoppers, store admins/sellers, riders, admins |
| `rider-orders` | Rider order/pending cross-domain order flow. | Riders, store admins/sellers |
| `rider-pending` | Rider pending order flow. | Riders, store admins/sellers |
| `riders` | Rider list/form management. | Riders, store admins/sellers |
| `romashop` | Roma/Raha storefront variant with products, cart, orders, riders, store, inventory, login, and payments. | Shoppers, store admins/sellers, riders, admins |
| `stelup_shop` | Stelup shop with login, seller/product admin, cart/checkout, orders, messages, proposals, and profile. | Shoppers, sellers, registered users |
| `stores` | Store list/form management. | Store admins/sellers |
| `task-tracker` | Task manager with projects, my tasks, time tracker, password vault, email client, and dashboard/reporting. | System administrators, developers, support operators |
| `tax-master` | Tax master/reference data management. | System administrators |
| `uicomponents` | Shared UI components, especially tag textbox. | App users, admins |
| `uom` | Unit-of-measure/reference data management. | System administrators |
| `userapp` | Login, password reset/change, profile edit, terms/privacy, and account/payment-adjacent flows. | Registered users |
| `userapp_social` | Social user app login/profile flows. | Registered users |

## Notes

- `davvag-stripe-1` uses `app..json`, not the standard `app.json` filename.
- Several apps are variants or copies of the same domain, especially CMS, shop, profile, and Raha/Romashop.
- User groups are inferred because the app descriptors do not consistently declare explicit role metadata.
- For route lists, service methods, dependencies, and component-level details, use `docs/apps_features_list.md`.
