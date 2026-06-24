# DAVVAG Apps Feature and User List

Generated from `davvag-core/localhost/apps` on 2026-06-23.

This is a source-derived inventory. Features are summarized from `app.json` descriptors, registered components, routes, subapps, and service component metadata. User groups are inferred from app names, titles, routes, and component names because most app descriptors do not declare explicit roles.

## Summary

- Top-level app folders scanned: 75
- Standard app descriptors (`app.json`): 74
- Alternate descriptors: `davvag-stripe-1/app..json`
- Missing descriptors: none
- Declared app services in app descriptors: 101
- Registered service-handler components found: 107
- Public PHP service methods found: 535

## App Catalog

| App folder | Title | Users | Feature summary | Services |
| --- | --- | --- | --- | --- |
| ai-agent-creator | AI Agent Creator | system administrators, developers/automation admins | AI agent and workflow tooling | declared: creator-api; registered handlers: AI Agent Creator API |
| ai-chatgpt-agent | AI ChatGPT Agent | system administrators, developers/automation admins | AI agent and workflow tooling | declared: agent-api; registered handlers: AI ChatGPT Agent API |
| ar-app | Augmented Reality | system administrators, media/content users | Reference data management, AR/media capture and viewing | declared: uom-handler |
| bible | Bible App | students/learners, teachers/course admins, system administrators | Reference data management | declared: uom-handler |
| bible-copy | Bible App | students/learners, teachers/course admins | Reader Bible | none |
| com_qti_students | Student Market Place | students/learners, teachers/course admins, customers/shoppers, store admins/sellers, registered users, media/content users | Product and inventory management, Payments, checkout, and finance workflows, User profile management, Donate Check Out, Donate Complete | declared: productsvr; registered handlers: productsvr |
| course-manager | Course Manager | students/learners, teachers/course admins, customers/shoppers, store admins/sellers, system administrators, registered users, media/content users | Dashboard, Courses, Timetable, Weekly Timetable, Attendance, Gradebook, Assignments, Dashboard and reporting, Course, student, and learning management, Product and inventory management | declared: api; registered handlers: api |
| currency-configuration | Currency Configuration | system administrators | Settings and configuration | declared: currency-configuration-handler; registered handlers: currency-configuration-handler |
| davvag-agent-flow | DAVVAG Agent Flow | system administrators, developers/automation admins | AI agent and workflow tooling | declared: flow-api; registered handlers: DAVVAG Agent Flow API |
| davvag-album | Davvag Album | content editors/site admins, media/content users | Catogories, New Catogory, Albums, New Album, CMS content and site presentation, Catogory Form, Catogory All | declared: cms-album-handler; registered handlers: cms-album-handler |
| davvag-app-manager | App Manager | system administrators | App Permission, Launchers, Schemas, Apps, Developer/admin tooling | declared: app-handler; registered handlers: app-handler |
| davvag-artical-app | Articals | content editors/site admins, media/content users | CMS content and site presentation | declared: cmsArtical-handler |
| davvag-attributes | Attribute Creator | system administrators | AI agent and workflow tooling, Developer/admin tooling | declared: app-handler, attribute-generator; registered handlers: app-handler |
| davvag-banking | Banking APP | finance/payment admins, media/content users | Payments, checkout, and finance workflows, Authentication and account pages, Settings and configuration | declared: app-handler; registered handlers: app-handler |
| davvag-banking-settings | Banking APP | system administrators, finance/payment admins, registered users, media/content users | Banks, Pending Diposits, Payments, checkout, and finance workflows, Authentication and account pages, User profile management, Settings and configuration | declared: app-handler; registered handlers: app-handler |
| davvag-cms | Davvag CMS 0.7 | students/learners, teachers/course admins, customers/shoppers, store admins/sellers, content editors/site admins, system administrators, registered users, media/content users | Dependencies, Soss Routes, App Popup, Soss Routes Vue, Soss Validator, Soss Uploader, Soss Data, Developer/admin tooling, CMS content and site presentation, AR/media capture and viewing | registered handlers: dynamic-attributes, dynamic-attributes, dynamic-attributes, product-handler, soss-uploader, soss-uploader |
| davvag-cms-davvag | Davvag | customers/shoppers, store admins/sellers, content editors/site admins, developers/automation admins, registered users, media/content users | Dependencies, Soss Routes, Soss Routes Vue, Soss Validator, Soss Uploader, Domain Info, Soss Data, CMS content and site presentation, AR/media capture and viewing, User profile management | registered handlers: product-handler, domain-info, productsvr, soss-uploader, soss-uploader |
| davvag-cms-generalapps | CMS | content editors/site admins, system administrators, media/content users | All Articals, New Artical, Buttons, New Button, Cards, New Card, Catogories, New Catogory, Albums, New Album | declared: cms-gapp-handler; registered handlers: cms-gapp-handler |
| davvag-cms-generalapps-v2 | CMS | content editors/site admins, system administrators, media/content users | All Articals, New Artical, Buttons, New Button, Cards, New Card, Catogories, New Catogory, Albums, New Album | declared: cms-gapp-handler; registered handlers: cms-gapp-handler |
| davvag-cms-v7 | Davvag CMS v7 | content editors/site admins, system administrators, media/content users | CMS content and site presentation, Developer/admin tooling, Soss Routes, App Popup, Soss Routes Vue, Soss Validator, Soss Uploader, Soss Data, AR/media capture and viewing, Theme Changer | declared: cms-v7-api; registered handlers: cms-v7-api, dynamic-attributes, dynamic-attributes, dynamic-attributes, product-handler, soss-uploader, soss-uploader |
| davvag-cms-v7-setting | Davvag CMS v7 Settings | content editors/site admins, system administrators | Settings and configuration | declared: settings-api; registered handlers: settings-api |
| davvag-directpay-lk | Direct Pay Payment Charge | finance/payment admins, media/content users | Payments, checkout, and finance workflows | declared: app-handler; registered handlers: app-handler |
| davvag-directpay-lk-settings | Direct Pay Settings | system administrators, finance/payment admins | Settings and configuration | declared: app-handler; registered handlers: app-handler |
| davvag-email-settings | Email Configuration | system administrators, developers/automation admins, marketing/support operators | Settings and configuration, Test Form | declared: app-handler; registered handlers: app-handler |
| davvag-etl-system | Sampe App | developers/automation admins | Sample Input Form, Test Form, Sample Popup | declared: app-handler; registered handlers: app-handler |
| davvag-flow-designer | Davvag Flow Designer | developers/automation admins | AI agent and workflow tooling | declared: flow-designer-api; registered handlers: flow-designer-api |
| davvag-global | Sampe App | system administrators | Sample Input Form | declared: api; registered handlers: app-handler |
| davvag-hosting-console | Hosting Manager | system administrators | Developer/admin tooling, Test Form, Sample Popup | declared: hosting-handler; registered handlers: app-handler |
| davvag-ipg | IPG Selector | finance/payment admins | Payments, checkout, and finance workflows, Test Form | declared: app-handler; registered handlers: app-handler |
| davvag-orders | Order Taking | customers/shoppers, store admins/sellers | Pending Orders, Pending Bids, Order and delivery operations | declared: davvag-order-handler; registered handlers: product-handler |
| davvag-sample-app-1 | Sampe App | app users, admins | Sample Input Form, Test Form, Sample Popup | declared: app-handler; registered handlers: app-handler |
| davvag-scheduler | Schedules | app users, admins | Tasks and scheduling | declared: schedules-handler; registered handlers: product-handler |
| davvag-shop | Davvag Market Place | customers/shoppers, store admins/sellers, media/content users | Product and inventory management, Payments, checkout, and finance workflows, Order and delivery operations | declared: productsvr; registered handlers: product-handler, productsvr |
| davvag-shop-v2 | Davvag Market Place | customers/shoppers, store admins/sellers, media/content users | Product and inventory management, Payments, checkout, and finance workflows, Order and delivery operations, Bid Cofirmation | declared: productsvr; registered handlers: product-handler, productsvr |
| davvag-sip | Sampe App | app users, admins | Sample Input Form, Test Form | declared: app-handler; registered handlers: app-handler |
| davvag-stripe | Strip Payment Gatway App | finance/payment admins, media/content users | Payments, checkout, and finance workflows, Authentication and account pages | declared: app-handler; registered handlers: app-handler |
| davvag-stripe-1 | Davvag Stripe IGP | finance/payment admins, media/content users | Payments, checkout, and finance workflows, Order and delivery operations | declared: stripe-ipg-handler; registered handlers: stripe-ipg-handler |
| davvag-task | Davvag Task | app users, admins | Task, Projects, Projects List, Tasks and scheduling, View Object, Project Type | declared: taskapi, viewObjectAPI; registered handlers: Task Api, View Object Api |
| davvag-tools | App Downloader | system administrators | Davvag Img Cropper, Davvag File Uploader, Davvag App Downloader, View Object, Capture | declared: davvag-img-cropper, davvag-file-uploader, davvag-app-downloader, viewObjectAPI; registered handlers: View Object Api |
| davvag-useradmin | Users | system administrators | Users, User Groups, User profile management, Groups | declared: user-handler; registered handlers: User Creation Service |
| dock | DAAVG Dock | students/learners, teachers/course admins, content editors/site admins, system administrators, registered users, media/content users | Dependencies, Soss Routes, Soss Data, Developer/admin tooling, Soss Routes Vue, Soss Validator, Soss Uploader, CMS content and site presentation, User profile management, AR/media capture and viewing | registered handlers: dynamic-attributes, dynamic-attributes, dynamic-attributes, product-handler, product-handler, dynamic-attributes, product-handler, soss-uploader, soss-uploader |
| dock-settings | Web Dock Settings | content editors/site admins, system administrators | Settings and configuration, User profile management | declared: settings-handler |
| ephraim | Ephraim App | developers/automation admins | Projects, Course, student, and learning management, Test Form, Sample Popup | declared: app-handler; registered handlers: app-handler |
| facebook-messanger | facebook Messager | marketing/support operators | Messaging and campaign tooling | declared: messager-platform; registered handlers: fb-messanger-platform |
| facebook-messanger-v1 | Facebook Messanger v1 | marketing/support operators | Messaging and campaign tooling, Brodcastform, Brodcast, Authentication and account pages | declared: keyword-handler, broadcaster-handler; registered handlers: product-handler, product-handler |
| facebooktest | UOM | system administrators, marketing/support operators | Reference data management | declared: uom-handler; registered handlers: product-handler |
| grn | GRN | app users, admins | Grn All, Grn Form | declared: grn-handler |
| i18n | Internationalization | app users, admins | I18n | declared: i18n |
| inventory | Inventory | customers/shoppers, store admins/sellers | Product and inventory management | declared: inventory-handler; registered handlers: product-handler |
| lbc-dashborad-report | LBC Dashbord | registered users, media/content users | Outstanding Report, All Profiles, All, Dashboard Enrolment, Dashboard Income, Dashboard and reporting, Customers | declared: rpt-handler; registered handlers: product-handler |
| lbc-study-app | LBC Study APP | students/learners, teachers/course admins, system administrators, registered users | Profile Search, Course, Settings, Sample Input Form, Settings and configuration, User profile management, Course, student, and learning management | declared: app-handler, profile; registered handlers: app-handler, profile |
| order | Orders | customers/shoppers, store admins/sellers | Order and delivery operations | declared: order-handler, inventory-handler; registered handlers: product-handler |
| pending-orders | pending-order | customers/shoppers, store admins/sellers, finance/payment admins, developers/automation admins | Order and delivery operations, Payments, checkout, and finance workflows, Product and inventory management | declared: crossdomainorder; registered handlers: crossdomainorder |
| product_marketplace | product_marketplace | customers/shoppers, store admins/sellers, media/content users | Product and inventory management | declared: product; registered handlers: product |
| productapp | Products | customers/shoppers, store admins/sellers | Product and inventory management | declared: product; registered handlers: product |
| productapp-v2 | Products V2 | customers/shoppers, store admins/sellers | Product and inventory management | declared: product; registered handlers: product |
| productcategories | Product Categories | customers/shoppers, store admins/sellers | Category All, Category Form | declared: category-handler |
| profile-catogory-creator | Profile Catogories | system administrators, registered users | User profile management | declared: profile-catogory-handler; registered handlers: profile-catogory-handler |
| profileapp | Profile | system administrators, finance/payment admins, registered users | User profile management, Payments, checkout, and finance workflows, Frm PO, Settings and configuration | declared: profile; registered handlers: profile |
| profileapp-admin | Profile Admin App | system administrators, finance/payment admins, registered users | Invoice Deletion, Payments, checkout, and finance workflows, Test Form | declared: app-handler; registered handlers: app-handler |
| profileapp.v1 | Profile V1 | system administrators, finance/payment admins, registered users | Profiles, Invoices, Receipts, Deposits, Collections, User profile management, Payments, checkout, and finance workflows, Frm PO, Settings and configuration | declared: profile; registered handlers: profile |
| qib-reg-app | QIB App | app users, admins | Course, student, and learning management, Result Upload, Sample Popup, Result | declared: app-handler; registered handlers: app-handler |
| raha | Raha.lk - Eat healthy | customers/shoppers, store admins/sellers, riders/delivery staff, content editors/site admins, system administrators, finance/payment admins, registered users, media/content users | Dependencies, Soss Routes, Soss Routes Vue, Soss Validator, Soss Uploader, Settings and configuration, AR/media capture and viewing, CMS content and site presentation, Payments, checkout, and finance workflows, User profile management | declared: soss-uploader, product-handler, order-handler, cart-handler, uom-handler, inventory-handler, store-handler, rider-handler, login-handler; registered handlers: product-handler, product-handler, product-handler, order-handler, product-handler, soss-uploader |
| rider-orders | pending-order | customers/shoppers, store admins/sellers, riders/delivery staff, finance/payment admins, developers/automation admins | Order and delivery operations, Payments, checkout, and finance workflows, Product and inventory management | declared: crossdomainorder; registered handlers: crossdomainorder |
| rider-pending | rider-pending | customers/shoppers, store admins/sellers, riders/delivery staff, finance/payment admins, developers/automation admins | Order and delivery operations, Payments, checkout, and finance workflows, Product and inventory management | declared: crossdomainorder; registered handlers: crossdomainorder |
| riders | Riders | customers/shoppers, store admins/sellers, riders/delivery staff | Order and delivery operations | declared: rider-handler |
| romashop | Raha.lk - Eat healthy | customers/shoppers, store admins/sellers, riders/delivery staff, content editors/site admins, system administrators, finance/payment admins, registered users, media/content users | Dependencies, Soss Routes, Soss Routes Vue, Soss Validator, Soss Uploader, Settings and configuration, AR/media capture and viewing, CMS content and site presentation, Payments, checkout, and finance workflows, User profile management | declared: soss-uploader, product-handler, order-handler, cart-handler, uom-handler, inventory-handler, store-handler, rider-handler, login-handler; registered handlers: product-handler, product-handler, product-handler, order-handler, product-handler, soss-uploader |
| stelup_shop | Stelup App | customers/shoppers, store admins/sellers, system administrators, registered users, media/content users | Sample Input Form, Authentication and account pages, Product and inventory management, CMS content and site presentation, AR/media capture and viewing, User profile management, Messaging, Payments, checkout, and finance workflows, Order and delivery operations, Accept Proposal | declared: login-handler, app-handler, productsvr, p_svr, seller_svr; registered handlers: app-handler, product-handler, productsvr, app-handler, seller Service |
| stores | Stores | customers/shoppers, store admins/sellers | Stores All, Stores Form | declared: store-handler |
| task-tracker | Task Manager | system administrators, developers/automation admins, marketing/support operators, media/content users | Projects, My Tasks, Time Tracker, Tasks and scheduling, Password Vault, Dashboard and reporting, Messaging and campaign tooling | declared: taskapi, passwordvaultapi, TaskEmailClient; registered handlers: passwordvaultapi, taskapi, TaskEmailClient |
| tax-master | Tax Master | system administrators | Reference data management | declared: tax-master-handler; registered handlers: tax-master-handler |
| uicomponents | UI Components | app users, admins | Tagtextbox, Dependencies | declared: tagtextbox |
| uom | UOM | system administrators | Reference data management | declared: uom-handler |
| userapp | User App | finance/payment admins, registered users | Authentication and account pages, Chnage Password, User profile management, Payments, checkout, and finance workflows | declared: login-handler; registered handlers: product-handler |
| userapp_social | User App | registered users | Authentication and account pages, User profile management | declared: login-handler; registered handlers: product-handler |

## User Groups

- admins: `davvag-sample-app-1`, `davvag-scheduler`, `davvag-sip`, `davvag-task`, `grn`, `i18n`, `qib-reg-app`, `uicomponents`
- app users: `davvag-sample-app-1`, `davvag-scheduler`, `davvag-sip`, `davvag-task`, `grn`, `i18n`, `qib-reg-app`, `uicomponents`
- content editors/site admins: `davvag-album`, `davvag-artical-app`, `davvag-cms`, `davvag-cms-davvag`, `davvag-cms-generalapps`, `davvag-cms-generalapps-v2`, `davvag-cms-v7`, `davvag-cms-v7-setting`, `dock`, `dock-settings`, `raha`, `romashop`
- customers/shoppers: `com_qti_students`, `course-manager`, `davvag-cms`, `davvag-cms-davvag`, `davvag-orders`, `davvag-shop`, `davvag-shop-v2`, `inventory`, `order`, `pending-orders`, `product_marketplace`, `productapp`, `productapp-v2`, `productcategories`, `raha`, `rider-orders`, `rider-pending`, `riders`, `romashop`, `stelup_shop`, `stores`
- developers/automation admins: `ai-agent-creator`, `ai-chatgpt-agent`, `davvag-agent-flow`, `davvag-cms-davvag`, `davvag-email-settings`, `davvag-etl-system`, `davvag-flow-designer`, `ephraim`, `pending-orders`, `rider-orders`, `rider-pending`, `task-tracker`
- finance/payment admins: `davvag-banking`, `davvag-banking-settings`, `davvag-directpay-lk`, `davvag-directpay-lk-settings`, `davvag-ipg`, `davvag-stripe`, `davvag-stripe-1`, `pending-orders`, `profileapp`, `profileapp-admin`, `profileapp.v1`, `raha`, `rider-orders`, `rider-pending`, `romashop`, `userapp`
- marketing/support operators: `davvag-email-settings`, `facebook-messanger`, `facebook-messanger-v1`, `facebooktest`, `task-tracker`
- media/content users: `ar-app`, `com_qti_students`, `course-manager`, `davvag-album`, `davvag-artical-app`, `davvag-banking`, `davvag-banking-settings`, `davvag-cms`, `davvag-cms-davvag`, `davvag-cms-generalapps`, `davvag-cms-generalapps-v2`, `davvag-cms-v7`, `davvag-directpay-lk`, `davvag-shop`, `davvag-shop-v2`, `davvag-stripe`, `davvag-stripe-1`, `dock`, `lbc-dashborad-report`, `product_marketplace`, `raha`, `romashop`, `stelup_shop`, `task-tracker`
- registered users: `com_qti_students`, `course-manager`, `davvag-banking-settings`, `davvag-cms`, `davvag-cms-davvag`, `dock`, `lbc-dashborad-report`, `lbc-study-app`, `profile-catogory-creator`, `profileapp`, `profileapp-admin`, `profileapp.v1`, `raha`, `romashop`, `stelup_shop`, `userapp`, `userapp_social`
- riders/delivery staff: `raha`, `rider-orders`, `rider-pending`, `riders`, `romashop`
- store admins/sellers: `com_qti_students`, `course-manager`, `davvag-cms`, `davvag-cms-davvag`, `davvag-orders`, `davvag-shop`, `davvag-shop-v2`, `inventory`, `order`, `pending-orders`, `product_marketplace`, `productapp`, `productapp-v2`, `productcategories`, `raha`, `rider-orders`, `rider-pending`, `riders`, `romashop`, `stelup_shop`, `stores`
- students/learners: `bible`, `bible-copy`, `com_qti_students`, `course-manager`, `davvag-cms`, `dock`, `lbc-study-app`
- system administrators: `ai-agent-creator`, `ai-chatgpt-agent`, `ar-app`, `bible`, `course-manager`, `currency-configuration`, `davvag-agent-flow`, `davvag-app-manager`, `davvag-attributes`, `davvag-banking-settings`, `davvag-cms`, `davvag-cms-generalapps`, `davvag-cms-generalapps-v2`, `davvag-cms-v7`, `davvag-cms-v7-setting`, `davvag-directpay-lk-settings`, `davvag-email-settings`, `davvag-global`, `davvag-hosting-console`, `davvag-tools`, `davvag-useradmin`, `dock`, `dock-settings`, `facebooktest`, `lbc-study-app`, `profile-catogory-creator`, `profileapp`, `profileapp-admin`, `profileapp.v1`, `raha`, `romashop`, `stelup_shop`, `task-tracker`, `tax-master`, `uom`
- teachers/course admins: `bible`, `bible-copy`, `com_qti_students`, `course-manager`, `davvag-cms`, `dock`, `lbc-study-app`

## App Details

### ai-agent-creator - AI Agent Creator

- Descriptor: `ai-agent-creator/app.json`
- Version/author: 0.2 / Davvag
- Tags: showindock
- Intended users: system administrators, developers/automation admins
- Main features: AI agent and workflow tooling
- Startup component: creator-console
- On-load components: creator-api
- Component counts: component: 1, service: 1
- Components: `creator-console (component)`, `creator-api (service)`
- Dependencies: php-extensions: curl
- Routes: `/ -> creator-console`
- Declared services: `creator-api`
- Registered service handlers:
  - AI Agent Creator API (`services/creator-api/component.json`): Validates AI provider settings and generates structured agent configuration objects.; class ai_agent_creator\CreatorService; methods: GenerateConfig [POST], SaveAgent [POST], ListAgents [GET], DeleteAgent [POST], TestAgent [POST], RunAgent [POST], InteractWithAgent [POST], ClearSession [POST], Providers [GET]
- PHP service files/methods: `services/creator-api/service.php: getProviders, getListAgents, postGenerateConfig, postSaveAgent, postDeleteAgent, postTestAgent, postRunAgent, postInteractWithAgent, postClearSession, interactWithAgent, runAgent`

### ai-chatgpt-agent - AI ChatGPT Agent

- Descriptor: `ai-chatgpt-agent/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: system administrators, developers/automation admins
- Main features: AI agent and workflow tooling
- Startup component: chat-console
- On-load components: agent-api
- Component counts: component: 1, service: 1
- Components: `chat-console (component)`, `agent-api (service)`
- Dependencies: php-extensions: curl
- Routes: `/ -> chat-console`
- Declared services: `agent-api`
- Registered service handlers:
  - AI ChatGPT Agent API (`services/agent-api/component.json`): Server-side service for storing AI agent configuration and calling the OpenAI Responses API.; class ai_chatgpt_agent\AgentService; methods: Config [GET], SaveConfig [POST], ClearConfig [POST], Chat [POST]
- PHP service files/methods: `services/agent-api/service.php: getConfig, postSaveConfig, postClearConfig, postChat`

### ar-app - Augmented Reality

- Descriptor: `ar-app/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock, showincms
- Intended users: system administrators, media/content users
- Main features: Reference data management, AR/media capture and viewing
- Startup component: objectviewer
- On-load components: uom-handler
- Component counts: service: 1, component: 5
- Components: `uom-handler (service)`, `uom-all (component)`, `ar-camara (component)`, `ar-camara-gltf (component)`, `image_capture (component)`, `objectviewer (component)`
- Dependencies: apps: i18n
- Routes: `/gltf -> ar-camara-gltf`, `/image -> image_capture`
- Declared services: `uom-handler`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### bible - Bible App

- Descriptor: `bible/app.json`
- Version/author: 0.2 / Davvag
- Tags: showindock, showincms
- Intended users: students/learners, teachers/course admins, system administrators
- Main features: Reference data management
- Startup component: uom-all
- On-load components: uom-handler
- Component counts: service: 1, component: 2
- Components: `uom-handler (service)`, `uom-all (component)`, `uom-form (component)`
- Dependencies: apps: i18n
- Routes: `/uom -> uom-form`
- Declared services: `uom-handler`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### bible-copy - Bible App

- Descriptor: `bible-copy/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: students/learners, teachers/course admins
- Main features: Reader Bible
- Startup component: reader-bible
- On-load components: none
- Component counts: component: 1
- Components: `reader-bible (component)`
- Dependencies: apps: i18n
- Routes: none listed in descriptor
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### com_qti_students - Student Market Place

- Descriptor: `com_qti_students/app.json`
- Version/author: 0.10 / Davvag
- Tags: showindock, showincms
- Intended users: students/learners, teachers/course admins, customers/shoppers, store admins/sellers, registered users, media/content users
- Main features: Product and inventory management, Payments, checkout, and finance workflows, User profile management, Donate Check Out, Donate Complete
- Startup component: frmproduct-list
- On-load components: productsvr
- Component counts: service: 1, component: 8
- Components: `productsvr (service)`, `frmproduct-list (component)`, `frmproduct (component)`, `newproduct (component)`, `partial-cart (component)`, `partial-cart-checkout (component)`, `frmprofile-view (component)`, `donate-check-out (component)`, `donate-complete (component)`
- Dependencies: apps: uom; schemas: payment_ext_request, profile, profiles_search, profilestatus; plugins: auth, notify, phpcache, profile, sossdata
- Routes: `/checkout -> partial-cart`, `/checkout-complete -> partial-cart-checkout`, `/profile -> frmprofile-view`, `/donate -> donate-check-out`, `/payment-varification -> donate-complete`
- Declared services: `productsvr`
- Registered service handlers:
  - productsvr (`services/productsvr/component.json`): Vue.JS Plugin for Soss Router; class ProductServices; methods: allProducts [GET] (page, size, cat, q), PaymentRequest [POST]
- PHP service files/methods: `services/productsvr/service.php: getAllProducts, postPaymentRequest`

### course-manager - Course Manager

- Descriptor: `course-manager/app.json`
- Version/author: 0.7 / DAVVAG
- Tags: showincms, showindock
- Intended users: students/learners, teachers/course admins, customers/shoppers, store admins/sellers, system administrators, registered users, media/content users
- Main features: Dashboard, Courses, Timetable, Weekly Timetable, Attendance, Gradebook, Assignments, Dashboard and reporting, Course, student, and learning management, Product and inventory management
- Startup component: dashboard
- On-load components: course-style, api
- Component counts: component: 10, service: 1
- Components: `dashboard (component)`, `courses (component)`, `product-list-popup (component)`, `timetable (component)`, `weekly-timetable (component)`, `attendance (component)`, `gradebook (component)`, `assignments (component)`, `profiles (component)`, `course-style (component)`, `api (service)`
- Dependencies: schemas: course_manager_course, course_manager_subject, course_manager_product, course_manager_classgrade, course_manager_enrollment, course_manager_timetable, course_manager_classroom, course_manager_attendance, course_manager_assignment, course_manager_submission, course_manager_assessment, course_manager_mark, course_manager_grading_scale, course_manager_notification, products, profile; plugins: auth, sossdata
- Dock subapps: `Dashboard`, `Courses`, `Timetable`, `Weekly Timetable`, `Attendance`, `Gradebook`, `Assignments`
- Routes: `/ -> dashboard`, `/dashboard -> dashboard`, `/courses -> courses`, `/timetable -> timetable`, `/weekly-timetable -> weekly-timetable`, `/attendance -> attendance`, `/gradebook -> gradebook`, `/assignments -> assignments`, `/profiles -> profiles`
- Declared services: `api`
- Registered service handlers:
  - api (`services/api/component.json`): Course Manager service handlers; class course_manager\ApiService; methods: Dashboard [POST], EndpointCatalog [GET], SeedSampleData [POST], ListProfiles [POST], ListCourses [POST], SaveCourse [POST], CreateCourse [POST], DeleteCourse [POST], ListSubjects [POST], SaveSubject [POST], DeleteSubject [POST], ListProducts [POST], ProductCatalog [POST], SaveProduct [POST], DeleteProduct [POST], ListClassGrades [POST], SaveClassGrade [POST], DeleteClassGrade [POST], ListEnrollments [POST], SaveEnrollment [POST], DeleteEnrollment [POST], BulkImportEnrollments [POST], ListClassrooms [POST], SaveClassroom [POST], DeleteClassroom [POST], ListTimetable [POST], WeeklyTimetable [POST], SaveTimetable [POST], CreateTimetable [POST], DeleteTimetable [POST], ListAttendance [POST], AttendanceRoster [POST], SaveAttendance [POST], RecordAttendance [POST], BulkRecordAttendance [POST], QrCheckIn [POST], ExportAttendanceCsv [POST], ListAssignments [POST], SaveAssignment [POST], CreateAssignment [POST], DeleteAssignment [POST], ListSubmissions [POST], SaveSubmission [POST], SubmitAssignment [POST], DeleteSubmission [POST], GradeSubmission [POST], ListAssessments [POST], SaveAssessment [POST], DeleteAssessment [POST], ListMarks [POST], SaveMark [POST], DeleteMark [POST], ListGradingScales [POST], SaveGradingScale [POST], CreateGradingScale [POST], DeleteGradingScale [POST], ComputeGrade [GET] (marks), RecomputeGrades [POST], FinalGrade [POST], QueueNotification [POST]
- PHP service files/methods: `services/api/service.php: getEndpointCatalog, postEndpointCatalog, getDashboard, postDashboard, postSeedSampleData, postListProfiles, getListCourses, postListCourses, postCreateCourse, postSaveCourse, postDeleteCourse, postListSubjects, postSaveSubject, postDeleteSubject, postListProducts, postProductCatalog, postSaveProduct, postDeleteProduct, postListClassGrades, postSaveClassGrade, postDeleteClassGrade, postListEnrollments, postSaveEnrollment, postDeleteEnrollment, ... 43 more`

### currency-configuration - Currency Configuration

- Descriptor: `currency-configuration/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: system administrators
- Main features: Settings and configuration
- Startup component: currency-configuration-admin
- On-load components: currency-configuration-handler
- Component counts: component: 1, service: 1
- Components: `currency-configuration-admin (component)`, `currency-configuration-handler (service)`
- Dependencies: plugins: phpcache, sossdata
- Routes: `/ -> currency-configuration-admin`
- Declared services: `currency-configuration-handler`
- Registered service handlers:
  - currency-configuration-handler (`services/currency-configuration-handler/component.json`): Currency configuration service; class currency_configuration\CurrencyConfigurationService; methods: List [GET], Active [GET], Default [GET], Save [POST]
- PHP service files/methods: `services/currency-configuration-handler/service.php: getList, getActive, getDefault, postSave`

### davvag-agent-flow - DAVVAG Agent Flow

- Descriptor: `davvag-agent-flow/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock, showincms
- Intended users: system administrators, developers/automation admins
- Main features: AI agent and workflow tooling
- Startup component: flow-console
- On-load components: flow-api
- Component counts: component: 1, service: 1
- Components: `flow-console (component)`, `flow-api (service)`
- Dependencies: apps: ai-agent-creator
- Routes: `/ -> flow-console`
- Declared services: `flow-api`
- Registered service handlers:
  - DAVVAG Agent Flow API (`services/flow-api/component.json`): Loads ai-agent-creator agents, connector definitions, and saved agent flows.; class davvag_agent_flow\FlowService; methods: Bootstrap [GET], ListAgents [GET], ListFlows [GET], SaveFlow [POST], DeleteFlow [POST], Simulate [POST], ConnectorPayload [POST], Webhook [POST]
- PHP service files/methods: `services/flow-api/service.php: getBootstrap, getListAgents, getListFlows, postSaveFlow, postDeleteFlow, postSimulate, postConnectorPayload, getWebhook, postWebhook`

### davvag-album - Davvag Album

- Descriptor: `davvag-album/app.json`
- Version/author: 4.0 / Davvag
- Tags: showindock, showincms
- Intended users: content editors/site admins, media/content users
- Main features: Catogories, New Catogory, Albums, New Album, CMS content and site presentation, Catogory Form, Catogory All
- Startup component: menu
- On-load components: cms-album-handler
- Component counts: service: 1, component: 6
- Components: `cms-album-handler (service)`, `Artical-view (component)`, `catogory-form (component)`, `catogory-all (component)`, `Album-all (component)`, `Album-form (component)`, `carousel-form (component)`
- Dependencies: apps: davvag-tools; schemas: d_album_carousel_dtl_v1, d_album_carousel_v1, d_cms_album_imagev1, d_cms_album_v1, d_cms_artical_v1; plugins: auth, phpcache, sossdata
- Dock subapps: `Catogories`, `New Catogory`, `Albums`, `New Album`
- Routes: `/cat -> catogory-form`, `/catall -> catogory-all`, `/a -> Artical-view`, `/albumall -> Album-all`, `/album -> Album-form`, `/carousel -> carousel-form`
- Declared services: `cms-album-handler`
- Registered service handlers:
  - cms-album-handler (`services/cms-album-handler/component.json`): Vue.JS Plugin for Soss Router; class ArticalService; methods: SaveAlbum [POST], DeleteAlbum [POST], Album [GET] (q), SaveCarousel [POST]
- PHP service files/methods: `services/cms-album-handler/service.php: postSaveCarousel, postDeleteAlbum, postSaveAlbum`

### davvag-app-manager - App Manager

- Descriptor: `davvag-app-manager/app.json`
- Version/author: 0.1 / Lasitha Senanayake
- Tags: showindock
- Intended users: system administrators
- Main features: App Permission, Launchers, Schemas, Apps, Developer/admin tooling
- Startup component: apps
- On-load components: app-handler
- Component counts: service: 1, component: 4
- Components: `app-handler (service)`, `apps (component)`, `launcher (component)`, `schemas (component)`, `schema-file (component)`
- Dependencies: schemas: davvag_launchers, davvag_launchers_perm, domain_permision, schedule_pending; plugins: davvag-attributes
- Dock subapps: `App Permission`, `Launchers`, `Schemas`
- Routes: `/app -> apps`, `/launcher -> launcher`, `/schemas -> schemas`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`services/app-handler/component.json`): Vue.JS Plugin for Soss Router; class BroadcastService; methods: SaveLauncher [POST], allApplications [GET] (Group), SetAccess [POST], UserGroups [GET], DeleteItem [POST], Apps [GET], Schemas [GET], ApplicationLaunchers [GET] (app, subapp), UserGroupsByLauncher [GET] (p_appid), UserGroupsLancherAccess [GET] (appid), LauncherParentApp [GET] (bid), SaveLauncherUserPerm [POST], SaveSchema [POST]
- PHP service files/methods: `services/app-handler/service.php: getApplicationLaunchers, postSaveLauncherUserPerm, getUserGroupsByLauncher, getUserGroupsLancherAccess, postSaveLauncher, getLauncherParentApp, getApps, postSaveSchema, getSchemas, getallApplications, getUserGroups, postSetAccess, postDeleteItem`

### davvag-artical-app - Articals

- Descriptor: `davvag-artical-app/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: content editors/site admins, media/content users
- Main features: CMS content and site presentation
- Startup component: Artical-form
- On-load components: cmsArtical-handler
- Component counts: service: 1, component: 2
- Components: `cmsArtical-handler (service)`, `Artical-all (component)`, `Artical-form (component)`
- Dependencies: none
- Routes: `/artical -> Artical-form`
- Declared services: `cmsArtical-handler`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### davvag-attributes - Attribute Creator

- Descriptor: `davvag-attributes/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: system administrators
- Main features: AI agent and workflow tooling, Developer/admin tooling
- Startup component: attribute-creator
- On-load components: app-handler, attribute-generator
- Component counts: component: 1, service: 2
- Components: `attribute-creator (component)`, `app-handler (service)`, `attribute-generator (service)`
- Dependencies: schemas: d_attributes; plugins: davvag-flow, sossdata
- Routes: none listed in descriptor
- Declared services: `app-handler`, `attribute-generator`
- Registered service handlers:
  - app-handler (`services/app-handler/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: Save [POST], GetDataSource [POST], Atrribute [GET] (id), Attribute [GET] (id), List [GET], WorkFlows [GET]
- PHP service files/methods: `services/app-handler/service.php: postGetDataSource, getList, getAtrribute, getAttribute, postSave, getWorkFlows`

### davvag-banking - Banking APP

- Descriptor: `davvag-banking/app.json`
- Version/author: 0.2 / Davvag
- Tags: showincms
- Intended users: finance/payment admins, media/content users
- Main features: Payments, checkout, and finance workflows, Authentication and account pages, Settings and configuration
- Startup component: charge-form
- On-load components: app-handler
- Component counts: component: 1, service: 1
- Components: `charge-form (component)`, `app-handler (service)`
- Dependencies: apps: davvag-tools; schemas: davvag_bank_disposits, davvag_banking; plugins: davvag-order, profile, sossdata
- Routes: `/mapstripe -> register-stripe`, `/settings -> bank-all`, `/bank -> bank-settings`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class DirectPay_IPG; methods: Order [GET] (id), ExtPaymentRequest [GET] (id), SaveBankDiposit [POST]
- PHP service files/methods: `service/app-handler/service.php: postSaveBankDiposit, getExtPaymentRequest, postPayment, getOrder`

### davvag-banking-settings - Banking APP

- Descriptor: `davvag-banking-settings/app.json`
- Version/author: 0.2 / Davvag
- Tags: showindock
- Intended users: system administrators, finance/payment admins, registered users, media/content users
- Main features: Banks, Pending Diposits, Payments, checkout, and finance workflows, Authentication and account pages, User profile management, Settings and configuration
- Startup component: bank-all
- On-load components: profile-style, app-handler
- Component counts: component: 6, service: 1
- Components: `charge-form (component)`, `register-stripe (component)`, `app-handler (service)`, `profile-style (component)`, `bank-settings (component)`, `bank-all (component)`, `bank-diposit-requests (component)`
- Dependencies: apps: davvag-tools; schemas: davvag_bank_disposits, davvag_bank_disposits_h, davvag_banking, davvag_directpay_lk; plugins: davvag-ipg, davvag-order, profile, sossdata
- Dock subapps: `Banks`, `Pending Diposits`
- Routes: `/settings -> bank-all`, `/bank -> bank-settings`, `/pendingdiposites -> bank-diposit-requests`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class Bank_IPG; methods: Save [POST], Payment [POST], Order [GET] (id), ExtPaymentRequest [GET] (id), PublicToken [GET] (id), allBanks [GET], Bank [GET] (bank_code), SaveBankDiposit [POST], PendingdipositsRequests [GET]
- PHP service files/methods: `service/app-handler/service.php: getPendingdipositsRequests, getallBanks, postSaveBankDiposit, postSave, getExtPaymentRequest, postPayment, getOrder, getBank, getPublicToken`

### davvag-cms - Davvag CMS 0.7

- Descriptor: `davvag-cms/app.json`
- Version/author: 0.71 / Daavg
- Tags: none
- Intended users: students/learners, teachers/course admins, customers/shoppers, store admins/sellers, content editors/site admins, system administrators, registered users, media/content users
- Main features: Dependencies, Soss Routes, App Popup, Soss Routes Vue, Soss Validator, Soss Uploader, Soss Data, Developer/admin tooling, CMS content and site presentation, AR/media capture and viewing
- Startup component: product
- On-load components: dependencies, app_popup, soss-routes, attribute_shell, attribute_shell_popup, soss-routes-vue, soss-uploader, soss-validator, auth-handler, soss-data
- Component counts: shell: 10, component: 4, partial: 12
- Components: `dependencies (shell)`, `soss-routes (shell)`, `app_popup (shell)`, `soss-routes-vue (shell)`, `soss-validator (shell)`, `soss-uploader (shell)`, `auth-handler (shell)`, `soss-data (shell)`, `attribute_shell_popup (shell)`, `attribute_shell (shell)`, `Artical-list (component)`, `left-menu (component)`, `footer-bar (component)`, `headerbar_launcher (component)`, `partial-home (partial)`, `partial-404 (partial)`, `partial-app (partial)`, `partial-account (partial)`, `partial-profile (partial)`, `partial-help (partial)`, `partial-homeblog-bg-carousel (partial)`, `partial-donation (partial)`, `partial-album-view (partial)`, `partial-album-bg-carousel (partial)`, ... 2 more
- Dependencies: apps: davvag-tools; schemas: davvag_launchers_query, davvag_launchers_subquery, profile_notify_u; plugins: auth, davvag-attributes, phpcache, profile, sossdata
- Routes: `/ -> partial-homeblog-bg`, `/app/@appName/*appRoute -> partial-app`, `/p/@username -> partial-user`, `/home -> partial-homeblog`, `/donate -> partial-donation`, `/notFound -> partial-404`, `/account -> partial-account`, `/profile -> partial-profile`, `/help -> partial-help`
- Registered service handlers:
  - dynamic-attributes (`shell/app_popup/component.json`): WEBDOCK Router; class appService; methods: Save [POST], uploadFile [POST]
  - dynamic-attributes (`shell/attribute_shell/component.json`): WEBDOCK Router; class appService; methods: Save [POST], Delete [POST], GetDataSource [POST], uploadFile [POST]
  - dynamic-attributes (`shell/attribute_shell_popup/component.json`): WEBDOCK Router; class appService; methods: Save [POST], uploadFile [POST]
  - product-handler (`shell/auth-handler/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), logout [GET], Session [GET] (token), resetPassword [GET] (email, token, password), getResetToken [GET] (email), Notification [GET], ClearNotiifcatiion [GET] (id), Launchers [GET] (appcode, component)
  - soss-uploader (`shell/soss-data/component.json`): Vue.JS Plugin for Soss Router; class SearchServices; methods: q [POST], Podq [POST], Settings [POST]
  - soss-uploader (`shell/soss-uploader/component.json`): Vue.JS Plugin for Soss Router; class UploaderService; methods: get [GET], upload [POST], upload_uncompressed [POST]
- PHP service files/methods: `shell/app_popup/service.php: postSave`; `shell/attribute_shell/service.php: postSave, postDelete, postGetDataSource`; `shell/attribute_shell_popup/service.php: postSave`; `shell/auth-handler/service.php: getSession, getLogin, getLogout, getGetResetToken, getResetPassword, getNotification, getLaunchers, getClearNotiifcatiion`; `shell/soss-data/service.php: postq, postPodq`; `shell/soss-uploader/service.php: __handle`

### davvag-cms-davvag - Davvag

- Descriptor: `davvag-cms-davvag/app.json`
- Version/author: 0.9 / Daavg
- Tags: none
- Intended users: customers/shoppers, store admins/sellers, content editors/site admins, developers/automation admins, registered users, media/content users
- Main features: Dependencies, Soss Routes, Soss Routes Vue, Soss Validator, Soss Uploader, Domain Info, Soss Data, CMS content and site presentation, AR/media capture and viewing, User profile management
- Startup component: product
- On-load components: dependencies, soss-routes, soss-routes-vue, soss-uploader, soss-validator, auth-handler, soss-data, domain-info, productsvr
- Component counts: shell: 9, component: 4, partial: 10
- Components: `dependencies (shell)`, `soss-routes (shell)`, `soss-routes-vue (shell)`, `soss-validator (shell)`, `soss-uploader (shell)`, `auth-handler (shell)`, `domain-info (shell)`, `soss-data (shell)`, `productsvr (shell)`, `Artical-list (component)`, `left-menu (component)`, `footer-bar (component)`, `headerbar (component)`, `partial-homeblogdetail (partial)`, `partial-404 (partial)`, `partial-app (partial)`, `partial-account (partial)`, `partial-profile (partial)`, `partial-help (partial)`, `partial-homedavvag (partial)`, `partial-donation (partial)`, `partial-shoping-view (partial)`, `partial-shoping-view_2 (partial)`
- Dependencies: schemas: nearproducts; plugins: auth, phpcache, sossdata
- Routes: `/ -> partial-homedavvag`, `/app/@appName/*appRoute -> partial-app`, `/home -> partial-homeblogdetail`, `/home2 -> partial-shoping-view_2`, `/donate -> partial-donation`, `/notFound -> partial-404`, `/account -> partial-account`, `/profile -> partial-profile`, `/help -> partial-help`
- Registered service handlers:
  - product-handler (`shell/auth-handler/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), logout [GET], Session [GET] (token), resetPassword [GET] (email, token, password), getResetToken [GET] (email)
  - domain-info (`shell/domain-info/component.json`): Vue.JS Plugin for Soss Router; class DomainInfoService; methods: DomainInfo [GET]
  - productsvr (`shell/productsvr/component.json`): Vue.JS Plugin for Soss Router; class ProductServices; methods: allProducts [GET] (page, size, q)
  - soss-uploader (`shell/soss-data/component.json`): Vue.JS Plugin for Soss Router; class SearchServices; methods: q [POST]
  - soss-uploader (`shell/soss-uploader/component.json`): Vue.JS Plugin for Soss Router; class UploaderService; methods: getFile [GET], uploadFile [POST]
- PHP service files/methods: `shell/auth-handler/service.php: getSession, getLogin, getLogout, getGetResetToken, getResetPassword`; `shell/domain-info/service.php: getDomainInfo`; `shell/productsvr/service.php: getAllProducts`; `shell/soss-data/service.php: postTransferInsertRequest, postTransferUpdateRequest, postTransferDeleteRequest, postq`; `shell/soss-uploader/service.php: __handle`

### davvag-cms-generalapps - CMS

- Descriptor: `davvag-cms-generalapps/app.json`
- Version/author: 1.8 / Davvag
- Tags: showindock, showincms
- Intended users: content editors/site admins, system administrators, media/content users
- Main features: All Articals, New Artical, Buttons, New Button, Cards, New Card, Catogories, New Catogory, Albums, New Album
- Startup component: menu
- On-load components: cms-gapp-handler
- Component counts: service: 1, component: 14
- Components: `cms-gapp-handler (service)`, `Artical-all (component)`, `Artical-view (component)`, `Artical-form (component)`, `carousel-form (component)`, `Button-form (component)`, `Button-all (component)`, `cards-form (component)`, `cards-all (component)`, `catogory-form (component)`, `catogory-all (component)`, `cms-settings (component)`, `menu (component)`, `Album-all (component)`, `Album-form (component)`
- Dependencies: apps: davvag-tools; schemas: d_cms_album_imagev1, d_cms_album_v1, d_cms_artical_imagev1, d_cms_artical_v1, d_cms_buttons_v1, d_cms_carousel_dtl_v1, d_cms_carousel_v1; plugins: auth, davvag-summary, phpcache, profile, sossdata
- Dock subapps: `All Articals`, `New Artical`, `Buttons`, `New Button`, `Cards`, `New Card`, `Catogories`, `New Catogory`, `Albums`, `New Album`, `Settings`
- Routes: `/artical -> Artical-form`, `/articalall -> Artical-all`, `/buttons -> Button-form`, `/buttonsall -> Button-all`, `/cards -> cards-form`, `/cardsall -> cards-all`, `/cat -> catogory-form`, `/catall -> catogory-all`, `/a -> Artical-view`, `/settings -> cms-settings`, `/albumall -> Album-all`, `/album -> Album-form`, `/carousel -> carousel-form`
- Declared services: `cms-gapp-handler`
- Registered service handlers:
  - cms-gapp-handler (`services/cms-gapp-handler/component.json`): Vue.JS Plugin for Soss Router; class ArticalService; methods: DeleteButton [POST], SaveAlbum [POST], SaveArtical [POST], Artical [GET] (q), Album [GET] (q), saveSettings [POST], Settings [POST], SaveCarousel [POST]
- PHP service files/methods: `services/cms-gapp-handler/service.php: postDeleteButton, postSaveArtical, postSaveAlbum, postSaveCarousel`

### davvag-cms-generalapps-v2 - CMS

- Descriptor: `davvag-cms-generalapps-v2/app.json`
- Version/author: 1.8 / Davvag
- Tags: showindock, showincms
- Intended users: content editors/site admins, system administrators, media/content users
- Main features: All Articals, New Artical, Buttons, New Button, Cards, New Card, Catogories, New Catogory, Albums, New Album
- Startup component: menu
- On-load components: cms-gapp-handler
- Component counts: service: 1, component: 14
- Components: `cms-gapp-handler (service)`, `Artical-all (component)`, `Artical-view (component)`, `Artical-form (component)`, `carousel-form (component)`, `Button-form (component)`, `Button-all (component)`, `cards-form (component)`, `cards-all (component)`, `catogory-form (component)`, `catogory-all (component)`, `cms-settings (component)`, `menu (component)`, `Album-all (component)`, `Album-form (component)`
- Dependencies: apps: davvag-tools; schemas: d_cms_album_imagev1, d_cms_album_v1, d_cms_artical_imagev1, d_cms_artical_v1, d_cms_buttons_v1, d_cms_carousel_dtl_v1, d_cms_carousel_v1; plugins: auth, davvag-summary, phpcache, profile, sossdata
- Dock subapps: `All Articals`, `New Artical`, `Buttons`, `New Button`, `Cards`, `New Card`, `Catogories`, `New Catogory`, `Albums`, `New Album`, `Settings`
- Routes: `/artical -> Artical-form`, `/articalall -> Artical-all`, `/buttons -> Button-form`, `/buttonsall -> Button-all`, `/cards -> cards-form`, `/cardsall -> cards-all`, `/cat -> catogory-form`, `/catall -> catogory-all`, `/a -> Artical-view`, `/settings -> cms-settings`, `/albumall -> Album-all`, `/album -> Album-form`, `/carousel -> carousel-form`
- Declared services: `cms-gapp-handler`
- Registered service handlers:
  - cms-gapp-handler (`services/cms-gapp-handler/component.json`): Vue.JS Plugin for Soss Router; class ArticalService; methods: DeleteButton [POST], SaveAlbum [POST], SaveArtical [POST], Artical [GET] (q), Album [GET] (q), saveSettings [POST], Settings [POST], SaveCarousel [POST]
- PHP service files/methods: `services/cms-gapp-handler/service.php: postDeleteButton, postSaveArtical, postSaveAlbum, postSaveCarousel`

### davvag-cms-v7 - Davvag CMS v7

- Descriptor: `davvag-cms-v7/app.json`
- Version/author: 0.8 / Davvag
- Tags: showincms, showindock
- Intended users: content editors/site admins, system administrators, media/content users
- Main features: CMS content and site presentation, Developer/admin tooling, Soss Routes, App Popup, Soss Routes Vue, Soss Validator, Soss Uploader, Soss Data, AR/media capture and viewing, Theme Changer
- Startup component: dock-shell
- On-load components: cms-v7-api, app_popup, soss-routes, attribute_shell, attribute_shell_popup, soss-uploader, soss-validator, auth-handler, soss-data, soss-routes-vue, nav-bar, footer-bar
- Component counts: component: 5, service: 1, shell: 9, partial: 5
- Components: `cms-apps (component)`, `dock-shell (component)`, `cms-v7-api (service)`, `soss-routes (shell)`, `app_popup (shell)`, `soss-routes-vue (shell)`, `soss-validator (shell)`, `soss-uploader (shell)`, `auth-handler (shell)`, `soss-data (shell)`, `attribute_shell_popup (shell)`, `attribute_shell (shell)`, `nav-bar (component)`, `footer-bar (component)`, `theme-changer (component)`, `home (partial)`, `features (partial)`, `contact (partial)`, `partial-app (partial)`, `partial-404 (partial)`
- Dependencies: apps: davvag-tools; schemas: davvag_launchers_query, davvag_launchers_subquery, profile_notify_u; plugins: auth, davvag-attributes, phpcache, profile, sossdata
- Routes: `/contact -> contact`, `/features -> features`, `/ -> home`, `/home -> home`, `/app/@appName/*appRoute -> partial-app`, `/not-found -> partial-404`, `/notFound -> partial-404`
- Declared services: `cms-v7-api`
- Registered service handlers:
  - cms-v7-api (`components/cms-v7-api/component.json`): Davvag CMS v7 content API; class davvag_cms_v7\CmsV7Api; methods: Site [GET], Page [GET] (slug), Assets [GET]
  - dynamic-attributes (`shell/app_popup/component.json`): WEBDOCK Router; class appService; methods: Save [POST], uploadFile [POST]
  - dynamic-attributes (`shell/attribute_shell/component.json`): WEBDOCK Router; class appService; methods: Save [POST], Delete [POST], GetDataSource [POST], uploadFile [POST]
  - dynamic-attributes (`shell/attribute_shell_popup/component.json`): WEBDOCK Router; class appService; methods: Save [POST], uploadFile [POST]
  - product-handler (`shell/auth-handler/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), logout [GET], Session [GET] (token), resetPassword [GET] (email, token, password), getResetToken [GET] (email), Notification [GET], ClearNotiifcatiion [GET] (id), Launchers [GET] (appcode, component)
  - soss-uploader (`shell/soss-data/component.json`): Vue.JS Plugin for Soss Router; class SearchServices; methods: q [POST], Podq [POST], Settings [POST]
  - soss-uploader (`shell/soss-uploader/component.json`): Vue.JS Plugin for Soss Router; class UploaderService; methods: get [GET], upload [POST], upload_uncompressed [POST]
- PHP service files/methods: `components/cms-v7-api/service.php: getSite, getPage, getAssets`; `shell/app_popup/service.php: postSave`; `shell/attribute_shell/service.php: postSave, postDelete, postGetDataSource`; `shell/attribute_shell_popup/service.php: postSave`; `shell/auth-handler/service.php: getSession, getLogin, getLogout, getGetResetToken, getResetPassword, getNotification, getLaunchers, getClearNotiifcatiion`; `shell/soss-data/service.php: postq, postPodq`; `shell/soss-uploader/service.php: __handle`

### davvag-cms-v7-setting - Davvag CMS v7 Settings

- Descriptor: `davvag-cms-v7-setting/app.json`
- Version/author: 0.3 / Davvag
- Tags: showindock
- Intended users: content editors/site admins, system administrators
- Main features: Settings and configuration
- Startup component: settings-console
- On-load components: settings-api
- Component counts: component: 1, service: 1
- Components: `settings-console (component)`, `settings-api (service)`
- Dependencies: apps: davvag-cms-v7
- Routes: `/ -> settings-console`
- Declared services: `settings-api`
- Registered service handlers:
  - settings-api (`services/settings-api/component.json`): Davvag CMS v7 settings API; class davvag_cms_v7_setting\CmsV7SettingsApi; methods: Site [GET], SaveSite [POST], Pages [GET], Page [GET] (slug), SavePage [POST], Assets [GET], UploadAsset [POST]
- PHP service files/methods: `services/settings-api/service.php: getSite, postSaveSite, getPages, getPage, postSavePage, getAssets, postUploadAsset`

### davvag-directpay-lk - Direct Pay Payment Charge

- Descriptor: `davvag-directpay-lk/app.json`
- Version/author: 0.6 / Davvag
- Tags: showincms
- Intended users: finance/payment admins, media/content users
- Main features: Payments, checkout, and finance workflows
- Startup component: charge-form
- On-load components: app-handler
- Component counts: component: 1, service: 1
- Components: `charge-form (component)`, `app-handler (service)`
- Dependencies: schemas: davvag_directpay_lk; plugins: davvag-order, profile, sossdata
- Routes: none listed in descriptor
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class DirectPay_IPG; methods: Payment [POST], Order [GET] (id), ExtPaymentRequest [GET] (id), PublicToken [GET] (id)
- PHP service files/methods: `service/app-handler/service.php: getExtPaymentRequest, postPayment, getOrder, getPublicToken`

### davvag-directpay-lk-settings - Direct Pay Settings

- Descriptor: `davvag-directpay-lk-settings/app.json`
- Version/author: 0.6 / Davvag
- Tags: showindock
- Intended users: system administrators, finance/payment admins
- Main features: Settings and configuration
- Startup component: directpay-settings
- On-load components: app-handler
- Component counts: service: 1, component: 1
- Components: `app-handler (service)`, `directpay-settings (component)`
- Dependencies: schemas: davvag_directpay_lk, davvag_ipgs; plugins: davvag-ipg, davvag-order, profile, sossdata
- Routes: `/settings -> directpay-settings`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class DirectPay_IPG; methods: Save [POST]
- PHP service files/methods: `service/app-handler/service.php: postSave`

### davvag-email-settings - Email Configuration

- Descriptor: `davvag-email-settings/app.json`
- Version/author: 0.1 / Davvag
- Tags: showincms, showindock
- Intended users: system administrators, developers/automation admins, marketing/support operators
- Main features: Settings and configuration, Test Form
- Startup component: email-settings
- On-load components: app-handler
- Component counts: component: 2, service: 1
- Components: `email-settings (component)`, `app-handler (service)`, `test-form (component)`
- Dependencies: apps: davvag-sample-app-1, stelup_shop; schemas: attr_lasitha_form, profile; workflows: davvag-attributes/testflow.json; plugins: davvag-attributes, davvag-flow, notify, profile, sossdata
- Routes: `/test -> test-form`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: Save [POST], Settings [GET], TestMail [GET], Test [GET]
- PHP service files/methods: `service/app-handler/service.php: postSave, getSettings, getTestMail, getTest`

### davvag-etl-system - Sampe App

- Descriptor: `davvag-etl-system/app.json`
- Version/author: 0.1 / Davvag
- Tags: showincms, showindock
- Intended users: developers/automation admins
- Main features: Sample Input Form, Test Form, Sample Popup
- Startup component: sample-input-form
- On-load components: app-handler
- Component counts: component: 3, service: 1
- Components: `sample-input-form (component)`, `app-handler (service)`, `test-form (component)`, `sample-popup (component)`
- Dependencies: apps: davvag-sample-app-1, stelup_shop; schemas: attr_lasitha_form, profile; workflows: davvag-attributes/testflow.json; plugins: davvag-attributes, davvag-flow, sossdata
- Routes: `/test -> test-form`, `/app -> sample-popup`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class davvag_sample_app_1\appService; methods: Save [POST]
- PHP service files/methods: `service/app-handler/service.php: postSave`

### davvag-flow-designer - Davvag Flow Designer

- Descriptor: `davvag-flow-designer/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock, showincms
- Intended users: developers/automation admins
- Main features: AI agent and workflow tooling
- Startup component: workflow-designer
- On-load components: flow-designer-api
- Component counts: component: 1, service: 1
- Components: `workflow-designer (component)`, `flow-designer-api (service)`
- Dependencies: none
- Routes: `/ -> workflow-designer`
- Declared services: `flow-designer-api`
- Registered service handlers:
  - flow-designer-api (`services/flow-designer-api/component.json`): Workflow file IO and toolbox discovery for Davvag Flow Designer.; class davvag_flow_designer\FlowDesignerService; methods: DesignerData [GET], ListWorkflows [GET], LoadWorkflow [POST], SaveWorkflow [POST], RunWorkflow [POST], DeleteWorkflow [POST]
- PHP service files/methods: `services/flow-designer-api/service.php: getDesignerData, getListWorkflows, postLoadWorkflow, postSaveWorkflow, postRunWorkflow, postDeleteWorkflow`

### davvag-global - Sampe App

- Descriptor: `davvag-global/app.json`
- Version/author: 0.1 / Davvag
- Tags: showincms, showindock
- Intended users: system administrators
- Main features: Sample Input Form
- Startup component: sample-input-form
- On-load components: app-handler
- Component counts: component: 1, service: 1
- Components: `sample-input-form (component)`, `api (service)`
- Dependencies: schemas: domain_registrar, domains_keytokens; plugins: sossdata
- Routes: `/test -> sample-input-form`
- Declared services: `api`
- Registered service handlers:
  - app-handler (`service/api/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: NewDomain [GET] (domain)
- PHP service files/methods: `service/api/service.php: getDomain`

### davvag-hosting-console - Hosting Manager

- Descriptor: `davvag-hosting-console/app.json`
- Version/author: 0.1 / Davvag
- Tags: showincms, showindock
- Intended users: system administrators
- Main features: Developer/admin tooling, Test Form, Sample Popup
- Startup component: console
- On-load components: hosting-handler
- Component counts: component: 5, service: 1
- Components: `console (component)`, `backup-app (component)`, `backup-files (component)`, `hosting-handler (service)`, `test-form (component)`, `sample-popup (component)`
- Dependencies: apps: davvag-sample-app-1, davvag-tools, stelup_shop; schemas: attr_lasitha_form, profile; workflows: davvag-attributes/testflow.json; plugins: davvag-attributes, davvag-flow
- Routes: `/test -> backup-files`, `/app -> sample-popup`
- Declared services: `hosting-handler`
- Registered service handlers:
  - app-handler (`service/hosting-handler/component.json`): Vue.JS Plugin for Soss Router; class hostingService; methods: BackupDatabase [GET], BackupSystem [GET], DataBackupFiles [GET], DeleteFile [POST], File [GET] (file)
- PHP service files/methods: `service/hosting-handler/service.php: postSave, getBackupDatabase, getBackupSystem, getDataBackupFiles, postDeleteFile, getFile`

### davvag-ipg - IPG Selector

- Descriptor: `davvag-ipg/app.json`
- Version/author: 0.1 / Davvag
- Tags: showincms
- Intended users: finance/payment admins
- Main features: Payments, checkout, and finance workflows, Test Form
- Startup component: payment-selector
- On-load components: app-handler
- Component counts: component: 2, service: 1
- Components: `payment-selector (component)`, `app-handler (service)`, `test-form (component)`
- Dependencies: apps: davvag-sample-app-1, stelup_shop; schemas: attr_lasitha_form, profile; workflows: davvag-attributes/testflow.json; plugins: davvag-attributes, davvag-flow, davvag-ipg, davvag-order, sossdata
- Routes: `/test -> test-form`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: Save [POST], IPGs [GET] (id, type)
- PHP service files/methods: `service/app-handler/service.php: postSave, getIPGs`

### davvag-orders - Order Taking

- Descriptor: `davvag-orders/app.json`
- Version/author: 0.1 / Lasitha Senanayake
- Tags: showindock, showincms
- Intended users: customers/shoppers, store admins/sellers
- Main features: Pending Orders, Pending Bids, Order and delivery operations
- Startup component: pending-orders
- On-load components: davvag-order-handler
- Component counts: service: 1, component: 2
- Components: `davvag-order-handler (service)`, `pending-orders (component)`, `pending-bids (component)`
- Dependencies: schemas: attr_bi, order_bid_approval_pending, orderdetails_accepted, orderdetails_pending, orderdetails_rejected, orderheader, orderheader_accepted, orderheader_pending, orderheader_rejected, product_bid_active, products, schedule_pending; plugins: auth, phpcache, profile, sossdata
- Dock subapps: `Pending Orders`, `Pending Bids`
- Routes: `/pendingorders -> pending-orders`, `/pendingbids -> pending-bids`
- Declared services: `davvag-order-handler`
- Registered service handlers:
  - product-handler (`services/davvag-order-handler/component.json`): Vue.JS Plugin for Soss Router; class BroadcastService; methods: AllPendingBids [GET], allPendingOrders [GET], PendingSchedulesBy [GET] (app, service, method), AcceptOrder [POST], RejectOrder [POST], RequestOrderCompletion [POST]
- PHP service files/methods: `services/davvag-order-handler/service.php: getAllPendingBids, postRequestOrderCompletion, getallPendingOrders, getPendingSchedulesBy, postAcceptOrder, postRejectOrder`

### davvag-sample-app-1 - Sampe App

- Descriptor: `davvag-sample-app-1/app.json`
- Version/author: 0.1 / Davvag
- Tags: showincms, showindock
- Intended users: app users, admins
- Main features: Sample Input Form, Test Form, Sample Popup
- Startup component: sample-input-form
- On-load components: app-handler
- Component counts: component: 3, service: 1
- Components: `sample-input-form (component)`, `app-handler (service)`, `test-form (component)`, `sample-popup (component)`
- Dependencies: apps: stelup_shop; schemas: attr_lasitha_form, profile; workflows: davvag-attributes/testflow.json; plugins: davvag-attributes, davvag-flow, sossdata
- Routes: `/test -> test-form`, `/app -> sample-popup`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class davvag_sample_app_1\appService; methods: Save [POST]
- PHP service files/methods: `service/app-handler/service.php: postSave`

### davvag-scheduler - Schedules

- Descriptor: `davvag-scheduler/app.json`
- Version/author: 0.1 / Lasitha Senanayake
- Tags: showindock
- Intended users: app users, admins
- Main features: Tasks and scheduling
- Startup component: schedules
- On-load components: schedules-handler
- Component counts: service: 1, component: 1
- Components: `schedules-handler (service)`, `schedules (component)`
- Dependencies: schemas: schedule_pending; plugins: auth, phpcache, sossdata
- Routes: `/keyword -> schedules`
- Declared services: `schedules-handler`
- Registered service handlers:
  - product-handler (`services/schedules-handler/component.json`): Vue.JS Plugin for Soss Router; class BroadcastService; methods: allPendingSchedules [GET], PendingSchedulesBy [GET] (app, service, method), DeleteItem [POST]
- PHP service files/methods: `services/schedules-handler/service.php: getallPendingSchedules, getPendingSchedulesBy, postDeleteItem`

### davvag-shop - Davvag Market Place

- Descriptor: `davvag-shop/app.json`
- Version/author: 0.8 / Davvag
- Tags: showincms
- Intended users: customers/shoppers, store admins/sellers, media/content users
- Main features: Product and inventory management, Payments, checkout, and finance workflows, Order and delivery operations
- Startup component: frmproduct-list
- On-load components: productsvr
- Component counts: service: 1, component: 6
- Components: `productsvr (service)`, `frmproduct-list (component)`, `frmproduct (component)`, `newproduct (component)`, `partial-cart (component)`, `partial-cart-checkout (component)`, `order-complete (component)`
- Dependencies: apps: uom; schemas: d_cms_artical_v1, nearproducts, orderdetails_pending, orderheader_pending, products; plugins: auth, davvag-order, phpcache, phpmailer, sossdata
- Routes: `/checkout -> partial-cart`, `/checkout-complete -> partial-cart-checkout`, `/order-complete -> order-complete`
- Declared services: `productsvr`
- Registered service handlers:
  - product-handler (`services/cart-handler/component.json`): Vue.JS Plugin for Soss Router; class CartService; methods: checkout [POST]
  - productsvr (`services/productsvr/component.json`): Vue.JS Plugin for Soss Router; class ProductServices; methods: allProducts [GET] (page, size, cat, q), Product [GET] (q), Checkout [POST]
- PHP service files/methods: `services/cart-handler/service.php: postCheckout, getGrn`; `services/productsvr/service.php: getAllProducts, postCheckout`

### davvag-shop-v2 - Davvag Market Place

- Descriptor: `davvag-shop-v2/app.json`
- Version/author: 0.8 / Davvag
- Tags: showincms
- Intended users: customers/shoppers, store admins/sellers, media/content users
- Main features: Product and inventory management, Payments, checkout, and finance workflows, Order and delivery operations, Bid Cofirmation
- Startup component: frmproduct-list
- On-load components: productsvr
- Component counts: service: 1, component: 9
- Components: `productsvr (service)`, `frmproduct-list (component)`, `frmproduct (component)`, `newproduct (component)`, `partial-cart (component)`, `partial-cart-checkout (component)`, `order-complete (component)`, `product_app (component)`, `bid-cofirmation (component)`, `product-catogory-list (component)`
- Dependencies: apps: davvag-tools, uom; schemas: attr_, attr_bi, d_cms_artical_v1, ds_products_v2, orderdetails_pending, orderheader_pending, productcat, products, products_attributes, products_bids, products_image; plugins: auth, davvag-order, phpcache, phpmailer, profile, sossdata
- Routes: `/checkout -> partial-cart`, `/checkout-complete -> partial-cart-checkout`, `/order-complete -> order-complete`, `/bid_confirmation -> bid-cofirmation`, `/cat-list -> product-catogory-list`
- Declared services: `productsvr`
- Registered service handlers:
  - product-handler (`services/cart-handler/component.json`): Vue.JS Plugin for Soss Router; class CartService; methods: checkout [POST]
  - productsvr (`services/productsvr/component.json`): Vue.JS Plugin for Soss Router; class ProductServices; methods: allProducts [GET] (page, size, cat, q), Product [GET] (q), Checkout [POST], ProductDetails [GET] (id), SaveBid [POST], AllProductCatList [GET] (id)
- PHP service files/methods: `services/cart-handler/service.php: postCheckout, getGrn`; `services/productsvr/service.php: getAllProductCatList, getAllProducts, postSaveBid, getProductDetails, postCheckout`

### davvag-sip - Sampe App

- Descriptor: `davvag-sip/app.json`
- Version/author: 0.1 / Davvag
- Tags: showincms, showindock
- Intended users: app users, admins
- Main features: Sample Input Form, Test Form
- Startup component: sample-input-form
- On-load components: app-handler
- Component counts: component: 2, service: 1
- Components: `sample-input-form (component)`, `app-handler (service)`, `test-form (component)`
- Dependencies: apps: davvag-sample-app-1, stelup_shop; schemas: attr_lasitha_form, profile; workflows: davvag-attributes/testflow.json; plugins: davvag-attributes, davvag-flow, sossdata
- Routes: `/test -> test-form`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: Save [POST]
- PHP service files/methods: `service/app-handler/service.php: postSave`

### davvag-stripe - Strip Payment Gatway App

- Descriptor: `davvag-stripe/app.json`
- Version/author: 0.1 / Davvag
- Tags: showincms
- Intended users: finance/payment admins, media/content users
- Main features: Payments, checkout, and finance workflows, Authentication and account pages
- Startup component: charge-form
- On-load components: app-handler
- Component counts: component: 2, service: 1
- Components: `charge-form (component)`, `register-stripe (component)`, `app-handler (service)`
- Dependencies: schemas: davvag_stripe; plugins: davvag-ipg, davvag-order, profile, sossdata, stripe
- Routes: `/mapstripe -> register-stripe`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class Stripe_IPG; methods: ChargeAmountFromCard [POST], TestChargeAmountFromCard [POST], Order [GET] (id), PublicToken [GET] (id)
- PHP service files/methods: `service/app-handler/service.php: getOrder, getPublicToken, postChargeAmountFromCard, postTestChargeAmountFromCard`

### davvag-stripe-1 - Davvag Stripe IGP

- Descriptor: `davvag-stripe-1/app..json` (app..json)
- Version/author: 0.8 / Lasitha Senanayake
- Tags: showincms
- Intended users: finance/payment admins, media/content users
- Main features: Payments, checkout, and finance workflows, Order and delivery operations
- Startup component: charge-form
- On-load components: stripe-ipg-handler
- Component counts: component: 1, service: 1
- Components: `charge-form (component)`, `stripe-ipg-handler (service)`
- Dependencies: none
- Routes: `/checkout -> partial-cart`, `/checkout-complete -> partial-cart-checkout`, `/order-complete -> order-complete`
- Declared services: `stripe-ipg-handler`
- Registered service handlers:
  - stripe-ipg-handler (`services/stripe-ipg-handler/component.json`): Stripe IPG Hangler; class Stripe_IPG; methods: none declared
- PHP service files/methods: `services/stripe-ipg-handler/service.php: addCustomer, postchargeAmountFromCard`

### davvag-task - Davvag Task

- Descriptor: `davvag-task/app.json`
- Version/author: 0.8 / Davvag
- Tags: showincms, showindock
- Intended users: app users, admins
- Main features: Task, Projects, Projects List, Tasks and scheduling, View Object, Project Type
- Startup component: TestApp
- On-load components: taskapi, viewObjectAPI
- Component counts: component: 5, service: 2
- Components: `projects-list (component)`, `projects (component)`, `tasks (component)`, `viewObject (component)`, `taskapi (service)`, `viewObjectAPI (service)`, `project-type (component)`
- Dependencies: schemas: davvag_task_header_active, davvag_task_project_types, davvag_task_projects, user_object, user_view_objects, usergroups, users
- Dock subapps: `Task`, `Projects`
- Routes: `/tasks -> tasks`, `/projects -> projects-list`
- Declared services: `taskapi`, `viewObjectAPI`
- Registered service handlers:
  - Task Api (`service/taskapi/component.json`): Stripe IPG Hangler; class TaskApi; methods: SaveTask [POST], SaveProject [POST], SaveType [POST], AllTypes [GET] (fromPage), AllProjects [GET] (fromPage), TypeByID [GET] (typeId), ProjectByID [GET] (projectId), TypesForProject [GET] (projectId)
  - View Object Api (`service/viewObjectAPI/component.json`): This allows to set permision to a record.; class ViewObjectApi; methods: PermisionValues [GET] (item_type), Save [POST], UserVieObjects [GET]
- PHP service files/methods: `service/taskapi/service.php: postSaveTask, postSaveProject, postSaveType, getAllTypes, getTypesForProject, getAllProjects, getTypeByID, getProjectByID`; `service/viewObjectAPI/service.php: postSave, getUserVieObjects, getPermisionValues`

### davvag-tools - App Downloader

- Descriptor: `davvag-tools/app.json`
- Version/author: 0.1 / Davvag
- Tags: showincms, showindock
- Intended users: system administrators
- Main features: Davvag Img Cropper, Davvag File Uploader, Davvag App Downloader, View Object, Capture
- Startup component: none
- On-load components: viewObjectAPI
- Component counts: service: 4, component: 2
- Components: `davvag-img-cropper (service)`, `davvag-file-uploader (service)`, `davvag-app-downloader (service)`, `viewObject (component)`, `capture (component)`, `viewObjectAPI (service)`
- Dependencies: schemas: user_object, user_view_objects, usergroups, users; plugins: auth, phpcache, sossdata
- Routes: `/viewObject -> viewObject`
- Declared services: `davvag-img-cropper`, `davvag-file-uploader`, `davvag-app-downloader`, `viewObjectAPI`
- Registered service handlers:
  - View Object Api (`services/viewObjectAPI/component.json`): This allows record permissions to be configured.; class ViewObjectApi; methods: PermisionValues [GET] (item_type), Save [POST], UserVieObjects [GET], FindObject [GET] (objectID)
- PHP service files/methods: `services/viewObjectAPI/service.php: postSave, getFindObject, getUserVieObjects, getPermisionValues`

### davvag-useradmin - Users

- Descriptor: `davvag-useradmin/app.json`
- Version/author: 0.1 / Lasitha Senanayake
- Tags: showindock
- Intended users: system administrators
- Main features: Users, User Groups, User profile management, Groups
- Startup component: users
- On-load components: user-handler, showincms
- Component counts: service: 1, component: 2
- Components: `user-handler (service)`, `users (component)`, `groups (component)`
- Dependencies: schemas: profile, schedule_pending, users, usergroups, domain_permision; plugins: auth, phpcache, sossdata
- Dock subapps: `Users`, `User Groups`
- Routes: `/keyword -> users`, `/users -> users`, `/groups -> groups`
- Declared services: `user-handler`
- Registered service handlers:
  - User Creation Service (`services/user-handler/component.json`): Vue.JS Plugin for Soss Router; class BroadcastService; methods: allusers [GET], SearchUsersByEmail [GET] (email), UserGroups [GET], NewUserGroup [GET] (groupid), ChangeGroup [GET] (userid, groupid), SaveUserGroup [POST], DeleteUserGroup [POST], AdminResetPassword [POST], RegisterUser [POST], DeleteItem [POST]
- PHP service files/methods: `services/user-handler/service.php: getallusers, getSearchUsersByEmail, postRegisterUser, getChangeGroup, getUserGroups, getNewUserGroup, postSaveUserGroup, postDeleteUserGroup, postAdminResetPassword, postDeleteItem`

### dock - DAAVG Dock

- Descriptor: `dock/app.json`
- Version/author: 0.3 / Daavg
- Tags: none
- Intended users: students/learners, teachers/course admins, content editors/site admins, system administrators, registered users, media/content users
- Main features: Dependencies, Soss Routes, Soss Data, Developer/admin tooling, Soss Routes Vue, Soss Validator, Soss Uploader, CMS content and site presentation, User profile management, AR/media capture and viewing
- Startup component: product
- On-load components: attribute_shell, app_popup, attribute_shell_popup, dependencies, login-handler, soss-routes, dynamic-attributes, soss-routes-vue, soss-uploader, soss-validator, auth-handler, soss-data
- Component counts: shell: 12, component: 5, partial: 7
- Components: `dependencies (shell)`, `soss-routes (shell)`, `login-handler (shell)`, `soss-data (shell)`, `dynamic-attributes (shell)`, `soss-routes-vue (shell)`, `soss-validator (shell)`, `soss-uploader (shell)`, `auth-handler (shell)`, `left-menu (component)`, `navigation-title (component)`, `headerbar (component)`, `partial-home (partial)`, `frmprofile-view (partial)`, `partial-404 (partial)`, `partial-app (partial)`, `partial-account (partial)`, `partial-profile (partial)`, `partial-help (partial)`, `userapp (component)`, `bible (component)`, `attribute_shell (shell)`, `attribute_shell_popup (shell)`, `app_popup (shell)`
- Dependencies: apps: davvag-tools; schemas: davvag_launchers_query, davvag_launchers_subquery, profile, profile_notify_u; plugins: auth, davvag-attributes, phpcache, profile, sossdata; php-extensions: curl
- Routes: `/ -> frmprofile-view`, `/app/@appName/*appRoute -> partial-app`, `/home -> frmprofile-view`, `/notFound -> partial-404`, `/account -> partial-account`, `/profile -> partial-profile`, `/help -> partial-help`
- Registered service handlers:
  - dynamic-attributes (`shell/app_popup/component.json`): WEBDOCK Router; class appService; methods: Save [POST], uploadFile [POST]
  - dynamic-attributes (`shell/attribute_shell/component.json`): WEBDOCK Router; class appService; methods: Save [POST], Delete [POST], GetDataSource [POST], uploadFile [POST]
  - dynamic-attributes (`shell/attribute_shell_popup/component.json`): WEBDOCK Router; class appService; methods: Save [POST], uploadFile [POST]
  - product-handler (`shell/auth-handler/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), logout [GET], Profile [GET], Session [GET] (token), resetPassword [GET] (email, token, password), getResetToken [GET] (email), Notification [GET], ClearNotiifcatiion [GET] (id), Launchers [GET] (appcode, component)
  - product-handler (`shell/auth-handler-old/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), Logout [GET], Session [GET] (token), resetPassword [GET] (email, token, password), getResetToken [GET] (email)
  - dynamic-attributes (`shell/dynamic-attributes/component.json`): WEBDOCK Router; class UploaderService; methods: getFile [GET], uploadFile [POST]
  - product-handler (`shell/login-handler/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), GoogleLogin [GET], FacebookLogin [GET], FacebookLoginCallback [GET], LoginState [GET], Logout [GET], getSession [GET] (token), resetPassword [GET] (email, token, password), ResetToken [GET] (email), registerUser [POST]
  - soss-uploader (`shell/soss-data/component.json`): Vue.JS Plugin for Soss Router; class SearchServices; methods: q [POST], Podq [POST], Settings [POST]
  - soss-uploader (`shell/soss-uploader/component.json`): Vue.JS Plugin for Soss Router; class UploaderService; methods: get [GET], upload [POST], upload_uncompressed [POST]
- PHP service files/methods: `shell/app_popup/service.php: postSave`; `shell/attribute_shell/service.php: postSave, postDelete, postGetDataSource`; `shell/attribute_shell_popup/service.php: postSave`; `shell/auth-handler/service.php: getSession, getProfile, getLogin, getLogout, getGetResetToken, getResetPassword, getNotification, getLaunchers, getClearNotiifcatiion`; `shell/auth-handler-old/service.php: getSession, getLogin, getLogout, getGetResetToken, getResetPassword, getNotification, getLaunchers, getClearNotiifcatiion`; `shell/soss-data/service.php: postq, postPodq`; `shell/soss-uploader/service.php: __handle`

### dock-settings - Web Dock Settings

- Descriptor: `dock-settings/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: content editors/site admins, system administrators
- Main features: Settings and configuration, User profile management
- Startup component: settingspage
- On-load components: settings-handler
- Component counts: service: 1, component: 2
- Components: `settings-handler (service)`, `settingspage (component)`, `user-form (component)`
- Dependencies: none
- Routes: `/user -> user-form`
- Declared services: `settings-handler`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### ephraim - Ephraim App

- Descriptor: `ephraim/app.json`
- Version/author: 0.1 / Lasitha
- Tags: showincms, showindock
- Intended users: developers/automation admins
- Main features: Projects, Course, student, and learning management, Test Form, Sample Popup
- Startup component: enrole-reg
- On-load components: app-handler
- Component counts: component: 4, service: 1
- Components: `enrole-reg (component)`, `app-handler (service)`, `test-form (component)`, `sample-popup (component)`, `projects (component)`
- Dependencies: apps: davvag-sample-app-1; schemas: attr_projects, eprahimprofilerequest, profile; plugins: davvag-attributes, mpdf, notify, sossdata
- Dock subapps: `Projects`
- Routes: `/entrole -> enrole-reg`, `/app -> sample-popup`, `/projects -> projects`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: Save [POST], RegxForm [GET], CSV [GET], Project [GET] (pid), ListOfPeople [GET] (pid)
- PHP service files/methods: `service/app-handler/service.php: postSave, getRegxForm, getListOfPeople, getProject, getCSV`

### facebook-messanger - facebook Messager

- Descriptor: `facebook-messanger/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: marketing/support operators
- Main features: Messaging and campaign tooling
- Startup component: keywordgenerator
- On-load components: keywordgenerator, messager-platform
- Component counts: service: 1, component: 1
- Components: `messager-platform (service)`, `keywordgenerator (component)`
- Dependencies: schemas: fb_keywords_detail_pod_flow, fb_messages, fb_profiles, profile; plugins: davvag-flow, phpcache, sossdata; php-extensions: curl
- Routes: `/keyword -> keywordgenerator`
- Declared services: `messager-platform`
- Registered service handlers:
  - fb-messanger-platform (`services/messager-platform/component.json`): Messanger Platform; class FBMessangerPlatform; methods: Webhook [GET]
- PHP service files/methods: none found

### facebook-messanger-v1 - Facebook Messanger v1

- Descriptor: `facebook-messanger-v1/app.json`
- Version/author: 0.1 / Lasitha Senanayake
- Tags: showindock
- Intended users: marketing/support operators
- Main features: Messaging and campaign tooling, Brodcastform, Brodcast, Authentication and account pages
- Startup component: keyword-list
- On-load components: keyword-handler, broadcaster-handler
- Component counts: service: 2, component: 6
- Components: `keyword-handler (service)`, `broadcaster-handler (service)`, `keyword (component)`, `keyword-list (component)`, `brodcastform (component)`, `Brodcast (component)`, `privacy-policy (component)`, `terms-conditions (component)`
- Dependencies: schemas: fb_broadcast, fb_keywords, fb_keywords_detail, fb_keywords_detail_pod_keysords, fb_profiles, schedule_pending; plugins: auth, davvag-flow, phpcache, sossdata
- Routes: `/keyword -> keyword`, `/broadcast -> brodcastform`, `/broadcastlist -> Brodcast`, `/privacy-policy -> privacy-policy`, `/terms-conditions -> terms-conditions`
- Declared services: `keyword-handler`, `broadcaster-handler`
- Registered service handlers:
  - product-handler (`services/broadcaster-handler/component.json`): Vue.JS Plugin for Soss Router; class BroadcastService; methods: allBroadcast [GET], SaveBroadcast [POST], BroadcastByID [GET] (id), SendMessage [GET] (id, page)
  - product-handler (`services/keyword-handler/component.json`): Vue.JS Plugin for Soss Router; class keywordService; methods: allKeywords [GET], SaveKeywords [POST], DavvagFlows [GET] (pageid), KeywordByID [GET] (id)
- PHP service files/methods: `services/broadcaster-handler/service.php: getallBroadcast, getSendMessage, postSaveBroadcast, getBroadcastByID`; `services/keyword-handler/service.php: getallKeywords, postSaveKeywords, getKeywordByID, getDavvagFlows`

### facebooktest - UOM

- Descriptor: `facebooktest/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: system administrators, marketing/support operators
- Main features: Reference data management
- Startup component: uom-all
- On-load components: uom-handler
- Component counts: service: 1, component: 2
- Components: `uom-handler (service)`, `uom-all (component)`, `uom-form (component)`
- Dependencies: plugins: auth, phpcache, sossdata
- Routes: `/uom -> uom-form`
- Declared services: `uom-handler`
- Registered service handlers:
  - product-handler (`services/uom-handler/component.json`): Vue.JS Plugin for Soss Router; class facebook; methods: callback [Get]
- PHP service files/methods: `services/uom-handler/service.php: getautherize, getcallback`

### grn - GRN

- Descriptor: `grn/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: app users, admins
- Main features: Grn All, Grn Form
- Startup component: grn-all
- On-load components: grn-handler
- Component counts: service: 1, component: 2
- Components: `grn-handler (service)`, `grn-all (component)`, `grn-form (component)`
- Dependencies: apps: inventory, productapp
- Routes: `/grn -> grn-form`
- Declared services: `grn-handler`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### i18n - Internationalization

- Descriptor: `i18n/app.json`
- Version/author: 0.1 / Davvag
- Tags: none
- Intended users: app users, admins
- Main features: I18n
- Startup component: none
- On-load components: none
- Component counts: service: 1
- Components: `i18n (service)`
- Dependencies: none
- Routes: none listed in descriptor
- Declared services: `i18n`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### inventory - Inventory

- Descriptor: `inventory/app.json`
- Version/author: 0.1 / Inventory
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers
- Main features: Product and inventory management
- Startup component: inventory
- On-load components: inventory-handler
- Component counts: service: 1, component: 1
- Components: `inventory-handler (service)`, `inventory (component)`
- Dependencies: plugins: phpmailer, transactions
- Routes: none listed in descriptor
- Declared services: `inventory-handler`
- Registered service handlers:
  - product-handler (`services/inventory-handler/component.json`): Vue.JS Plugin for Soss Router; class GrnService; methods: updateOrder [POST], newGrn [POST], allInventory [GET], allOrders [GET], nextDayOrders [GET], pendingOrders [GET], closedOrders [GET], cancledOrders [GET], dispatchedOrders [GET], riderOrders [GET] (riderid)
- PHP service files/methods: `services/inventory-handler/service.php: postUpdateOrder, getAllOrders, getRiderOrders, getCancledOrders, getPendingOrders, getNextDayOrders, getDispatchedOrders, getClosedOrders, getAllInventory, postNewGrn, getGrn`

### lbc-dashborad-report - LBC Dashbord

- Descriptor: `lbc-dashborad-report/app.json`
- Version/author: 0.6 / Lasitha Senanayake
- Tags: showindock
- Intended users: registered users, media/content users
- Main features: Outstanding Report, All Profiles, All, Dashboard Enrolment, Dashboard Income, Dashboard and reporting, Customers
- Startup component: Collection-Report
- On-load components: rpt-handler
- Component counts: service: 1, component: 6
- Components: `rpt-handler (service)`, `Collection-Report (component)`, `MonthlyDashboard (component)`, `MonthlyDashboard-income (component)`, `customers (component)`, `Profilelist-Report (component)`, `Profilelist-Report-all (component)`
- Dependencies: schemas: lbc_rpt_all_invoice_reciept, lbc_rpt_all_invoice_reciept_all_status, lbc_rpt_outstanding, orderdetails_purchase_sum_by_month, schedule_pending; plugins: auth, phpcache, sossdata
- Dock subapps: `Outstanding Report`, `All Profiles`, `All`, `Dashboard Enrolment`, `Dashboard Income`
- Routes: `/collection -> Collection-Report`, `/allprofiles -> Profilelist-Report`, `/all -> Profilelist-Report-all`, `/rpt-monthly-dashboard -> MonthlyDashboard`, `/rpt-monthly-dashboard-income -> MonthlyDashboard-income`
- Declared services: `rpt-handler`
- Registered service handlers:
  - product-handler (`services/rpt-handler/component.json`): Vue.JS Plugin for Soss Router; class rptService; methods: allOutstandingProfiles [GET] (page, size), allProfiles [GET] (page, size), CustomerEnrolment [GET] (startdate, enddate), allProfiles_withoutfilter [GET] (page, size), DeleteItem [POST]
- PHP service files/methods: `services/rpt-handler/service.php: getallOutstandingProfiles, getCustomerEnrolment, getallProfiles, getallProfiles_withoutfilter, getPendingSchedulesBy, postDeleteItem`

### lbc-study-app - LBC Study APP

- Descriptor: `lbc-study-app/app.json`
- Version/author: 0.3 / Lasitha Senanayake
- Tags: showincms, showindock
- Intended users: students/learners, teachers/course admins, system administrators, registered users
- Main features: Profile Search, Course, Settings, Sample Input Form, Settings and configuration, User profile management, Course, student, and learning management
- Startup component: sample-input-form
- On-load components: profile, app-handler
- Component counts: component: 8, service: 2
- Components: `sample-input-form (component)`, `app-handler (service)`, `Setting-form (component)`, `profile (service)`, `frmprofile-list (component)`, `frmprofile-view (component)`, `course_creation (component)`, `subject_creation (component)`, `enrol-course (component)`, `enrol-subject (component)`
- Dependencies: apps: davvag-sample-app-1, stelup_shop; schemas: attr_course_creation, attr_lasitha_form, attr_lbc_entrollments, attr_subject_creation, lbc_course_entrolments, lbc_course_entrolments_active, profile, profile_attributes, profiles_search_1, profileservices; workflows: davvag-attributes/testflow.json; plugins: auth, davvag-attributes, davvag-flow, phpcache, profile, sossdata
- Dock subapps: `Profile Search`, `Course`, `Settings`
- Routes: `/view -> frmprofile-view`, `/serach -> frmprofile-list`, `/course_creation -> course_creation`, `/subject_creation -> subject_creation`, `/settings -> Setting-form`, `/enrol -> enrol-course`, `/enrol_subject -> enrol-subject`
- Declared services: `app-handler`, `profile`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: Save [POST], SaveEntrol [POST], SaveEntrolSubjects [POST], Courses [GET] (id), Subjects [GET] (id)
  - profile (`service/profile/component.json`): Vue.JS Plugin for Soss Router; class ProfileService; methods: Search [GET] (q), SearchV1 [GET] (column, value), ByID [GET] (id), SupplierData [GET], q [POST], ChangeStatus [POST]
- PHP service files/methods: `service/app-handler/service.php: postSave, postSaveEntrol, postSaveEntrolSubjects, getActiveEnrolments, getCourses, getSubjects`; `service/profile/service.php: getSearchV1, getSearch, getActiveEnrolments, getByID, postq, postChangeStatus`

### order - Orders

- Descriptor: `order/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers
- Main features: Order and delivery operations
- Startup component: order-all
- On-load components: order-handler
- Component counts: service: 2, component: 2
- Components: `order-handler (service)`, `inventory-handler (service)`, `order-all (component)`, `order-nextday (component)`
- Dependencies: apps: inventory, riders; plugins: phpmailer, transactions
- Routes: `/nextday -> order-nextday`
- Declared services: `order-handler`, `inventory-handler`
- Registered service handlers:
  - product-handler (`services/inventory-handler/component.json`): Vue.JS Plugin for Soss Router; class GrnService; methods: updateOrder [POST], newGrn [POST], allInventory [GET], allOrders [GET], nextDayOrders [GET], pendingOrders [GET], closedOrders [GET], cancledOrders [GET], dispatchedOrders [GET], riderOrders [GET] (riderid)
- PHP service files/methods: `services/inventory-handler/service.php: postUpdateOrder, getAllOrders, getRiderOrders, getCancledOrders, getPendingOrders, getNextDayOrders, getDispatchedOrders, getClosedOrders, getAllInventory, postNewGrn, getGrn`

### pending-orders - pending-order

- Descriptor: `pending-orders/app.json`
- Version/author: 0.3 / Davvag
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers, finance/payment admins, developers/automation admins
- Main features: Order and delivery operations, Payments, checkout, and finance workflows, Product and inventory management
- Startup component: order-list
- On-load components: crossdomainorder
- Component counts: service: 1, component: 4
- Components: `crossdomainorder (service)`, `order-list (component)`, `frmInvoice-view (component)`, `frmproduct (component)`, `newproduct (component)`
- Dependencies: apps: uom; schemas: ledger, orderdetails, orderheader, product_inventrymaster, profile, profilestatus, rider_orders_pending; plugins: auth, phpcache, sossdata
- Routes: `/product -> newproduct`, `/invoice -> frmInvoice-view`
- Declared services: `crossdomainorder`
- Registered service handlers:
  - crossdomainorder (`services/crossdomainorder/component.json`): Vue.JS Plugin for Soss Router; class OrderService; methods: AllPendingOrders [GET], ApproveOrder [POST], RejectOrder [POST]
- PHP service files/methods: `services/crossdomainorder/service.php: getAllPendingOrders, postApproveOrder, postRejectOrder`

### product_marketplace - product_marketplace

- Descriptor: `product_marketplace/app.json`
- Version/author: 0.3 / Davvag
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers, media/content users
- Main features: Product and inventory management
- Startup component: frmproduct-list
- On-load components: product
- Component counts: service: 1, component: 3
- Components: `product (service)`, `frmproduct-list (component)`, `frmproduct (component)`, `newproduct (component)`
- Dependencies: apps: uom; schemas: d_all_summery, d_cms_artical_v1, products, products_attributes, products_image; plugins: auth, phpcache, sossdata
- Routes: `/product -> newproduct`
- Declared services: `product`
- Registered service handlers:
  - product (`services/product/component.json`): Vue.JS Plugin for Soss Router; class ProductService; methods: allProducts [GET] (page, size, q), Save [POST]
- PHP service files/methods: `services/product/service.php: postSave, getAllProducts`

### productapp - Products

- Descriptor: `productapp/app.json`
- Version/author: 0.6 / Davvag
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers
- Main features: Product and inventory management
- Startup component: frmproduct-list-V1
- On-load components: product
- Component counts: service: 1, component: 4
- Components: `product (service)`, `frmproduct-list-V1 (component)`, `frmproduct (component)`, `newproduct (component)`, `newproduct_publish (component)`
- Dependencies: apps: uom; schemas: d_all_summery, d_cms_artical_v1, nearproducts, product_published, product_search_1, products, products_attributes, products_deleted, products_image, profile; plugins: auth, phpcache, sossdata
- Routes: `/product -> newproduct`, `/publish -> newproduct_publish`
- Declared services: `product`
- Registered service handlers:
  - product (`services/product/component.json`): Vue.JS Plugin for Soss Router; class ProductService; methods: allProducts [GET] (page, size, q), Save [POST], Delete [POST], ProductToStore [POST], ProductSearch [POST]
- PHP service files/methods: `services/product/service.php: postProductSearch, postDelete, postSave, getAllProducts, postProductToStore`

### productapp-v2 - Products V2

- Descriptor: `productapp-v2/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers
- Main features: Product and inventory management
- Startup component: frmproduct-list
- On-load components: product-style, product
- Component counts: service: 1, component: 6
- Components: `product (service)`, `product-style (component)`, `frmproduct-list (component)`, `frmproduct (component)`, `newproduct (component)`, `newproduct_publish (component)`, `newproduct-v2 (component)`
- Dependencies: apps: davvag-tools; schemas: d_all_summery, d_cms_artical_v1, nearproducts, product_published, products, products_attributes, products_image; plugins: auth, davvag-attributes, phpcache, profile, sossdata
- Routes: `/product -> newproduct-v2`, `/publish -> newproduct_publish`
- Declared services: `product`
- Registered service handlers:
  - product (`services/product/component.json`): Vue.JS Plugin for Soss Router; class ProductService; methods: allProducts [GET] (page, size, q), Save [POST], Delete [POST], ProductToStore [POST]
- PHP service files/methods: `services/product/service.php: postDelete, postSave, getAllProducts, postProductToStore`

### productcategories - Product Categories

- Descriptor: `productcategories/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers
- Main features: Category All, Category Form
- Startup component: category-all
- On-load components: category-handler
- Component counts: service: 1, component: 2
- Components: `category-handler (service)`, `category-all (component)`, `category-form (component)`
- Dependencies: apps: productapp
- Routes: `/category -> category-form`
- Declared services: `category-handler`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### profile-catogory-creator - Profile Catogories

- Descriptor: `profile-catogory-creator/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: system administrators, registered users
- Main features: User profile management
- Startup component: profile-catogory-admin
- On-load components: profile-catogory-handler
- Component counts: component: 1, service: 1
- Components: `profile-catogory-admin (component)`, `profile-catogory-handler (service)`
- Dependencies: plugins: phpcache, sossdata
- Routes: `/ -> profile-catogory-admin`
- Declared services: `profile-catogory-handler`
- Registered service handlers:
  - profile-catogory-handler (`services/profile-catogory-handler/component.json`): Profile catogory service; class profile_catogory_creator\ProfileCatogoryService; methods: List [GET], Active [GET], Save [POST]
- PHP service files/methods: `services/profile-catogory-handler/service.php: getList, getActive, postSave`

### profileapp - Profile

- Descriptor: `profileapp/app.json`
- Version/author: 0.24 / Davvag
- Tags: showindock
- Intended users: system administrators, finance/payment admins, registered users
- Main features: User profile management, Payments, checkout, and finance workflows, Frm PO, Settings and configuration
- Startup component: frmprofile-list
- On-load components: profile
- Component counts: service: 1, component: 13
- Components: `profile (service)`, `frmProfile (component)`, `frmInvoice (component)`, `frmDiposit (component)`, `frmDiposit-view-print (component)`, `frmPO (component)`, `frmInvoice-view-print (component)`, `frmRecipt (component)`, `frmRecipt-view-print (component)`, `frmprofile-view (component)`, `frmprofile-list (component)`, `frmprofile-list-popup (component)`, `setting-app (component)`, `frmprofile-change-status (component)`
- Dependencies: apps: davvag-tools; schemas: attr_profile_service_activation, grndetails, grnheader, internal_ledger, internal_profilestatus, ledger, orderdetails, orderheader, podetails, poheader, product_inventrymaster, profile, profile_attributes, profiles_search_1, profileservices, profilestatus; plugins: auth, davvag-attributes, davvag-order, phpcache, profile, sossdata
- Routes: `/view -> frmprofile-view`, `/edit -> frmProfile`, `/inv -> frmInvoice`, `/dip -> frmDiposit`, `/rpt -> frmRecipt`, `/po -> frmPO`, `/receipt -> frmRecipt-view-print`, `/invoice -> frmInvoice-view-print`, `/diposit -> frmDiposit-view-print`, `/diposit_tr -> frmDiposit-view-print`, `/deposit -> frmDiposit-view-print`, `/deposit_tr -> frmDiposit-view-print`, `/deposit_de -> frmDiposit-view-print`, `/deposit_dv -> frmDiposit-view-print`, `/change -> frmprofile-change-status`
- Declared services: `profile`
- Registered service handlers:
  - profile (`services/profile/component.json`): Vue.JS Plugin for Soss Router; class ProfileService; methods: Save [POST], DipositSave [POST], DepositCancelation [GET] (id), InvoiceSave [POST], POSave [POST], GRNSave [POST], PaymentSave [POST], Search [GET] (q), SearchV1 [GET] (column, value), ByID [GET] (id), SupplierData [GET], q [POST], ChangeStatus [POST]
- PHP service files/methods: `services/profile/service.php: getSupplierData, postDipositSave, getDepositCancelation, postInvoiceSave, postPOSave, postGRNSave, postPaymentSave, postSave, getSearch, getSearchV1, getByID, postq, postChangeStatus`

### profileapp-admin - Profile Admin App

- Descriptor: `profileapp-admin/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: system administrators, finance/payment admins, registered users
- Main features: Invoice Deletion, Payments, checkout, and finance workflows, Test Form
- Startup component: none
- On-load components: app-handler
- Component counts: component: 2, service: 1
- Components: `Invoice-reversal (component)`, `app-handler (service)`, `test-form (component)`
- Dependencies: apps: davvag-sample-app-1, stelup_shop; schemas: attr_lasitha_form, profile; workflows: davvag-attributes/testflow.json; plugins: davvag-attributes, davvag-flow, davvag-order, profile, sossdata
- Dock subapps: `Invoice Deletion`
- Routes: `/Invoice-reversal -> Invoice-reversal`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: Save [POST], Delete [POST]
- PHP service files/methods: `service/app-handler/service.php: postSave, postDelete`

### profileapp.v1 - Profile V1

- Descriptor: `profileapp.v1/app.json`
- Version/author: 0.26 / Davvag
- Tags: showindock
- Intended users: system administrators, finance/payment admins, registered users
- Main features: Profiles, Invoices, Receipts, Deposits, Collections, User profile management, Payments, checkout, and finance workflows, Frm PO, Settings and configuration
- Startup component: frmprofile-list
- On-load components: profile-style, profile
- Component counts: service: 1, component: 15
- Components: `profile (service)`, `profile-style (component)`, `frmProfile (component)`, `frmInvoice (component)`, `frmDiposit (component)`, `frmDiposit-view-print (component)`, `frmPO (component)`, `frmInvoice-view-print (component)`, `frmRecipt (component)`, `frmRecipt-view-print (component)`, `frmTransaction-list (component)`, `frmprofile-view (component)`, `frmprofile-list (component)`, `frmprofile-list-popup (component)`, `setting-app (component)`, `frmprofile-change-status (component)`
- Dependencies: apps: davvag-tools; schemas: attr_profile_service_activation, currency_configuration, grndetails, grnheader, internal_ledger, internal_profilestatus, ledger, orderdetails, orderheader, payment_advance, paymentdetails, paymentheader, podetails, poheader, product_inventrymaster, profile, profile_attributes, profile_catogory, profiles_search_1, profileservices, profilestatus, tax_master; plugins: auth, davvag-attributes, davvag-order, phpcache, profile, sossdata
- Dock subapps: `Profiles`, `Invoices`, `Receipts`, `Deposits`, `Collections`
- Routes: `/view -> frmprofile-view`, `/edit -> frmProfile`, `/inv -> frmInvoice`, `/dip -> frmDiposit`, `/rpt -> frmRecipt`, `/po -> frmPO`, `/invoices -> frmTransaction-list`, `/receipts -> frmTransaction-list`, `/reciepts -> frmTransaction-list`, `/deposits -> frmTransaction-list`, `/diposits -> frmTransaction-list`, `/collections -> frmTransaction-list`, `/col -> frmTransaction-list`, `/receipt -> frmRecipt-view-print`, `/invoice -> frmInvoice-view-print`, `/diposit -> frmDiposit-view-print`, `/diposit_tr -> frmDiposit-view-print`, `/deposit -> frmDiposit-view-print`, ... 5 more
- Declared services: `profile`
- Registered service handlers:
  - profile (`services/profile/component.json`): Vue.JS Plugin for Soss Router; class ProfileService; methods: Save [POST], DipositSave [POST], DepositCancelation [GET] (id), ReceiptCancelation [GET] (id), InvoiceCancelation [GET] (id), InvoiceSave [POST], POSave [POST], GRNSave [POST], PaymentSave [POST], Search [GET] (q), SearchV1 [GET] (column, value), ByID [GET] (id), SupplierData [GET], ProfileCatogories [GET], InvoiceTaxes [GET], CurrencyConfig [GET], q [POST], ChangeStatus [POST]
- PHP service files/methods: `services/profile/service.php: getSupplierData, getProfileCatogories, getInvoiceTaxes, getCurrencyConfig, postTransactionList, postDipositSave, getDepositCancelation, getReceiptCancelation, getInvoiceCancelation, postInvoiceSave, postPOSave, postGRNSave, postPaymentSave, postSave, getSearch, getSearchV1, getByID, postq, postChangeStatus`

### qib-reg-app - QIB App

- Descriptor: `qib-reg-app/app.json`
- Version/author: 0.1 / Lasitha
- Tags: showincms, showindock
- Intended users: app users, admins
- Main features: Course, student, and learning management, Result Upload, Sample Popup, Result
- Startup component: enrole
- On-load components: app-handler
- Component counts: component: 4, service: 1
- Components: `enrole (component)`, `app-handler (service)`, `result-upload (component)`, `sample-popup (component)`, `result (component)`
- Dependencies: apps: davvag-sample-app-1, stelup_shop; schemas: attr_lasitha_form, profile, qibprofilerequest, qibprofilerequest_results; workflows: davvag-attributes/testflow.json; plugins: davvag-attributes, davvag-flow, mpdf, notify, sossdata
- Routes: `/result-upload -> result-upload`, `/result -> result`
- Declared services: `app-handler`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: Save [POST], PDF [GET], CSV [GET], Results [GET] (refid), ResultPDF [GET] (ref), UploadExcel [POST]
- PHP service files/methods: `service/app-handler/service.php: postUploadExcel, postSave, getResults, getResultPDF, getPDF, getCSV`

### raha - Raha.lk - Eat healthy

- Descriptor: `raha/app.json`
- Version/author: 0.2 / Daavg
- Tags: none
- Intended users: customers/shoppers, store admins/sellers, riders/delivery staff, content editors/site admins, system administrators, finance/payment admins, registered users, media/content users
- Main features: Dependencies, Soss Routes, Soss Routes Vue, Soss Validator, Soss Uploader, Settings and configuration, AR/media capture and viewing, CMS content and site presentation, Payments, checkout, and finance workflows, User profile management
- Startup component: product
- On-load components: dependencies, order-handler, product-handler, uom-handler, rider-handler, inventory-handler, login-handler, store-handler, cart-handler, soss-uploader, soss-routes, soss-routes-vue, soss-validator, config
- Component counts: shell: 5, service: 9, partial: 18, component: 4
- Components: `dependencies (shell)`, `soss-routes (shell)`, `soss-routes-vue (shell)`, `soss-validator (shell)`, `soss-uploader (service)`, `config (shell)`, `partial-404 (partial)`, `partial-about (partial)`, `partial-contact (partial)`, `partial-cart (partial)`, `partial-cart-checkout (partial)`, `partial-cart-paycomplete (partial)`, `partial-cart-payerror (partial)`, `partial-cart-paysuccess (partial)`, `partial-cart-payment (partial)`, `partial-user (partial)`, `partial-user-register-success (partial)`, `partial-home (partial)`, `partial-mobile-welcome (partial)`, `partial-mobile-login (partial)`, `partial-forget-password (partial)`, `partial-reset-password (partial)`, `partial-rider-orders (partial)`, `partial-rider-login (partial)`, ... 12 more
- Dependencies: apps: profileapp; schemas: nearproducts, orderdetails_accepted, orderdetails_pending, orderdetails_rejected, orderheader_accepted, orderheader_pending, orderheader_rejected, profile, store_products; plugins: auth, phpmailer, sossdata, transactions
- Routes: none listed in descriptor
- Declared services: `soss-uploader`, `product-handler`, `order-handler`, `cart-handler`, `uom-handler`, `inventory-handler`, `store-handler`, `rider-handler`, `login-handler`
- Registered service handlers:
  - product-handler (`domain/handlers/cart-handler/component.json`): Vue.JS Plugin for Soss Router; class CartService; methods: checkout [POST]
  - product-handler (`domain/handlers/inventory-handler/component.json`): Vue.JS Plugin for Soss Router; class GrnService; methods: updateOrder [POST], newGrn [POST], allInventory [GET], allOrders [GET], nextDayOrders [GET], pendingOrders [GET], closedOrders [GET], cancledOrders [GET], dispatchedOrders [GET], riderOrders [GET] (riderid)
  - product-handler (`domain/handlers/login-handler/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), logout [GET], getSession [GET] (token), resetPassword [GET] (email, token, password), getResetToken [GET] (email)
  - order-handler (`domain/handlers/order-handler/component.json`): Vue.JS Plugin for Soss Router; class OrderService; methods: AllPendingOrder [GET] (lat, lng, catid)
  - product-handler (`domain/handlers/product-handler/component.json`): Vue.JS Plugin for Soss Router; class ProductService; methods: allProducts [GET] (lat, lng, catid), ProductToStore [POST]
  - soss-uploader (`shell/soss-uploader/component.json`): Vue.JS Plugin for Soss Router; class UploaderService; methods: getFile [GET], uploadFile [POST]
- PHP service files/methods: `domain/handlers/cart-handler/service.php: postCheckout, getGrn`; `domain/handlers/inventory-handler/service.php: postUpdateOrder, getAllOrders, getRiderOrders, getCancledOrders, getPendingOrders, getNextDayOrders, getDispatchedOrders, getClosedOrders, getAllInventory, postNewGrn, getGrn`; `domain/handlers/login-handler/service.php: postRegisterUser, getGetSession, getLogin, getLogout, getGetResetToken, getResetPassword`; `domain/handlers/order-handler/service.php: getAllPendingOrder, postOrderChange`; `domain/handlers/product-handler/service.php: postProductToStore, getAllProducts`; `shell/soss-uploader/service.php: __handle`

### rider-orders - pending-order

- Descriptor: `rider-orders/app.json`
- Version/author: 0.3 / Davvag
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers, riders/delivery staff, finance/payment admins, developers/automation admins
- Main features: Order and delivery operations, Payments, checkout, and finance workflows, Product and inventory management
- Startup component: order-list
- On-load components: crossdomainorder
- Component counts: service: 1, component: 4
- Components: `crossdomainorder (service)`, `order-list (component)`, `frmInvoice-view (component)`, `frmproduct (component)`, `newproduct (component)`
- Dependencies: apps: uom; schemas: ledger, orderdetails, orderheader, product_inventrymaster, profile, profilestatus; plugins: auth, phpcache, sossdata
- Routes: `/product -> newproduct`, `/invoice -> frmInvoice-view`
- Declared services: `crossdomainorder`
- Registered service handlers:
  - crossdomainorder (`services/crossdomainorder/component.json`): Vue.JS Plugin for Soss Router; class OrderService; methods: AllPendingOrders [GET], ApproveOrder [POST], RejectOrder [POST]
- PHP service files/methods: `services/crossdomainorder/service.php: getAllPendingOrders, postApproveOrder, postRejectOrder`

### rider-pending - rider-pending

- Descriptor: `rider-pending/app.json`
- Version/author: 0.3 / Davvag
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers, riders/delivery staff, finance/payment admins, developers/automation admins
- Main features: Order and delivery operations, Payments, checkout, and finance workflows, Product and inventory management
- Startup component: order-list
- On-load components: crossdomainorder
- Component counts: service: 1, component: 4
- Components: `crossdomainorder (service)`, `order-list (component)`, `frmInvoice-view (component)`, `frmproduct (component)`, `newproduct (component)`
- Dependencies: apps: uom; schemas: ledger, orderdetails, orderheader, product_inventrymaster, profile, profilestatus, rider_orders_pending; plugins: auth, phpcache, sossdata
- Routes: `/product -> newproduct`, `/invoice -> frmInvoice-view`
- Declared services: `crossdomainorder`
- Registered service handlers:
  - crossdomainorder (`services/crossdomainorder/component.json`): Vue.JS Plugin for Soss Router; class OrderService; methods: AllPendingOrders [GET], ApproveOrder [POST], RejectOrder [POST]
- PHP service files/methods: `services/crossdomainorder/service.php: getAllPendingOrders, postApproveOrder, postRejectOrder`

### riders - Riders

- Descriptor: `riders/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers, riders/delivery staff
- Main features: Order and delivery operations
- Startup component: riders-all
- On-load components: rider-handler
- Component counts: service: 1, component: 2
- Components: `rider-handler (service)`, `riders-all (component)`, `riders-form (component)`
- Dependencies: none
- Routes: `/rider -> riders-form`
- Declared services: `rider-handler`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### romashop - Raha.lk - Eat healthy

- Descriptor: `romashop/app.json`
- Version/author: 0.2 / Daavg
- Tags: none
- Intended users: customers/shoppers, store admins/sellers, riders/delivery staff, content editors/site admins, system administrators, finance/payment admins, registered users, media/content users
- Main features: Dependencies, Soss Routes, Soss Routes Vue, Soss Validator, Soss Uploader, Settings and configuration, AR/media capture and viewing, CMS content and site presentation, Payments, checkout, and finance workflows, User profile management
- Startup component: product
- On-load components: dependencies, order-handler, product-handler, uom-handler, rider-handler, inventory-handler, login-handler, store-handler, cart-handler, soss-uploader, soss-routes, soss-routes-vue, soss-validator, config
- Component counts: shell: 5, service: 9, partial: 18, component: 4
- Components: `dependencies (shell)`, `soss-routes (shell)`, `soss-routes-vue (shell)`, `soss-validator (shell)`, `soss-uploader (service)`, `config (shell)`, `partial-404 (partial)`, `partial-about (partial)`, `partial-contact (partial)`, `partial-cart (partial)`, `partial-cart-checkout (partial)`, `partial-cart-paycomplete (partial)`, `partial-cart-payerror (partial)`, `partial-cart-paysuccess (partial)`, `partial-cart-payment (partial)`, `partial-user (partial)`, `partial-user-register-success (partial)`, `partial-home (partial)`, `partial-mobile-welcome (partial)`, `partial-mobile-login (partial)`, `partial-forget-password (partial)`, `partial-reset-password (partial)`, `partial-rider-orders (partial)`, `partial-rider-login (partial)`, ... 12 more
- Dependencies: apps: profileapp; schemas: nearproducts, orderdetails_accepted, orderdetails_pending, orderdetails_rejected, orderheader_accepted, orderheader_pending, orderheader_rejected, profile, store_products; plugins: auth, phpmailer, sossdata, transactions
- Routes: none listed in descriptor
- Declared services: `soss-uploader`, `product-handler`, `order-handler`, `cart-handler`, `uom-handler`, `inventory-handler`, `store-handler`, `rider-handler`, `login-handler`
- Registered service handlers:
  - product-handler (`domain/handlers/cart-handler/component.json`): Vue.JS Plugin for Soss Router; class CartService; methods: checkout [POST]
  - product-handler (`domain/handlers/inventory-handler/component.json`): Vue.JS Plugin for Soss Router; class GrnService; methods: updateOrder [POST], newGrn [POST], allInventory [GET], allOrders [GET], nextDayOrders [GET], pendingOrders [GET], closedOrders [GET], cancledOrders [GET], dispatchedOrders [GET], riderOrders [GET] (riderid)
  - product-handler (`domain/handlers/login-handler/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), logout [GET], getSession [GET] (token), resetPassword [GET] (email, token, password), getResetToken [GET] (email)
  - order-handler (`domain/handlers/order-handler/component.json`): Vue.JS Plugin for Soss Router; class OrderService; methods: AllPendingOrder [GET] (lat, lng, catid)
  - product-handler (`domain/handlers/product-handler/component.json`): Vue.JS Plugin for Soss Router; class ProductService; methods: allProducts [GET] (lat, lng, catid), ProductToStore [POST]
  - soss-uploader (`shell/soss-uploader/component.json`): Vue.JS Plugin for Soss Router; class UploaderService; methods: getFile [GET], uploadFile [POST]
- PHP service files/methods: `domain/handlers/cart-handler/service.php: postCheckout, getGrn`; `domain/handlers/inventory-handler/service.php: postUpdateOrder, getAllOrders, getRiderOrders, getCancledOrders, getPendingOrders, getNextDayOrders, getDispatchedOrders, getClosedOrders, getAllInventory, postNewGrn, getGrn`; `domain/handlers/login-handler/service.php: postRegisterUser, getGetSession, getLogin, getLogout, getGetResetToken, getResetPassword`; `domain/handlers/order-handler/service.php: getAllPendingOrder, postOrderChange`; `domain/handlers/product-handler/service.php: postProductToStore, getAllProducts`; `shell/soss-uploader/service.php: __handle`

### stelup_shop - Stelup App

- Descriptor: `stelup_shop/app.json`
- Version/author: 0.4 / lasitha@davvag.com
- Tags: showincms
- Intended users: customers/shoppers, store admins/sellers, system administrators, registered users, media/content users
- Main features: Sample Input Form, Authentication and account pages, Product and inventory management, CMS content and site presentation, AR/media capture and viewing, User profile management, Messaging, Payments, checkout, and finance workflows, Order and delivery operations, Accept Proposal
- Startup component: home-sp
- On-load components: app-handler, p_svr, productsvr, login-handler, seller_svr
- Component counts: service: 5, component: 23
- Components: `login-handler (service)`, `app-handler (service)`, `productsvr (service)`, `p_svr (service)`, `seller_svr (service)`, `sample-input-form (component)`, `seller-register-1 (component)`, `product-register (component)`, `frmproduct-list (component)`, `home-sp (component)`, `partial-404 (component)`, `profile (component)`, `messaging (component)`, `product-comments (component)`, `cart-checkout (component)`, `partial-cart (component)`, `order-complete (component)`, `product-trade (component)`, `accept_proposal (component)`, `rejected_proposal (component)`, `accepted_proposal (component)`, `product_app (component)`, `product-group-sell (component)`, `messages (component)`, ... 4 more
- Dependencies: apps: davvag-tools; schemas: attr_stelup_dispatch, d_cms_artical_v1, ledger, messages, messages_inbox_query, messages_query, nearproducts, orderdetails, orderdetails_pending, orderheader_accepted, orderheader_cancelled, orderheader_deleted, orderheader_dispatched, orderheader_pending, orderheader_rejected, payment_ext_request, products, products_attributes, products_comments, products_favorites, products_image, products_likes, products_reviews, products_stelup_1, products_stelup_2, products_stelup_3, products_subitems, products_subitems_query, profile, profile_attributes, profile_followers, profile_policy, profilestatus, stelup_trade, stelup_trade_confirm, stelup_trade_rejected; workflows: davvag-attributes/stelup_dispatch_order.json; plugins: Facebook, Google, auth, davvag-attributes, davvag-flow, davvag-order, notify, phpcache, profile, profile-stelup, sossdata
- Routes: `/test -> sample-input-form`, `/xplo -> frmproduct-list`, `/selleronboard -> seller-register-1`, `/itemonboard -> product-group-sell`, `/checkout -> cart-checkout`, `/checkout-cart -> partial-cart`, `/profile -> profile`, `/payment-gateway -> `, `/order-complete -> order-complete`, `/traditem -> product-trade`, `/group-sell -> product-group-sell`, `/messages -> messages`, `/userprofile -> user-profile`, `/selleradmin -> seller-product-admin`, `/orders -> pending-orders`
- Declared services: `login-handler`, `app-handler`, `productsvr`, `p_svr`, `seller_svr`
- Registered service handlers:
  - app-handler (`service/app-handler/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: Save [POST], SaveProduct [POST], ConfirmProductProposal [POST], RejectProductProposal [POST], Like [POST], Favorite [POST], allProducts [GET] (page, size, pid, q), Comment [POST], AllComments [GET] (id), SaveProductProposal [POST], Product [GET] (itemid)
  - product-handler (`service/login-handler/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), GoogleLogin [GET], FacebookLogin [GET], FacebookLoginCallback [GET], LoginState [GET], Logout [GET], getSession [GET] (token), resetPassword [GET] (email, token, password), ResetToken [GET] (email), registerUser [POST], ChangePassword [POST], updatePolicy [POST], ProfileData [GET], Save [POST], CreatePaymentRequest [POST]
  - productsvr (`service/productsvr/component.json`): Vue.JS Plugin for Soss Router; class ProductServices; methods: allProducts [GET] (page, size, cat, q), Product [GET] (q), Checkout [POST]
  - app-handler (`service/p_svr/component.json`): Vue.JS Plugin for Soss Router; class appService; methods: Follow [POST], Profile [GET] (id), allProducts [GET] (page, size, pid, id, tid), SendMessage [POST], Messages [GET] (id, page, size, lastid), MessageInbox [GET], SellerProducts [GET]
  - seller Service (`service/seller_svr/component.json`): Vue.JS Plugin for Soss Router; class seller_svr; methods: Orders [GET] (type), OrderDetails [GET] (id), UpdateOrder [GET] (id, status)
- PHP service files/methods: `service/app-handler/service.php: postLike, postComment, getAllComments, postFavorite, postSave, postConfirmProductProposal, postRejectProductProposal, postSaveProductProposal, postSaveProduct, getAllProducts`; `service/login-handler/service.php: postSave, getGoogleLogin, getLoginState, postCreatePaymentRequest, getProfileData, getFacebookLogin, getFacebookLoginCallback, postRegisterUser, postupdatePolicy, getGetSession, getLogin, getLogout, postChangePassword, getResetToken, getResetPassword`; `service/productsvr/service.php: getAllProducts, postCheckout`; `service/p_svr/service.php: postFollow, getProfile, postSendMessage, getMessageInbox, getMessages, getAllProducts, getSellerProducts`

### stores - Stores

- Descriptor: `stores/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: customers/shoppers, store admins/sellers
- Main features: Stores All, Stores Form
- Startup component: stores-all
- On-load components: store-handler
- Component counts: service: 1, component: 2
- Components: `store-handler (service)`, `stores-all (component)`, `stores-form (component)`
- Dependencies: none
- Routes: `/store -> stores-form`
- Declared services: `store-handler`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### task-tracker - Task Manager

- Descriptor: `task-tracker/app.json`
- Version/author: 2.1 / DAVVAG
- Tags: showincms, showindock
- Intended users: system administrators, developers/automation admins, marketing/support operators, media/content users
- Main features: Projects, My Tasks, Time Tracker, Tasks and scheduling, Password Vault, Dashboard and reporting, Messaging and campaign tooling
- Startup component: projects
- On-load components: task-style, taskapi, passwordvaultapi
- Component counts: component: 8, service: 3
- Components: `projects (component)`, `tasks (component)`, `my-tasks (component)`, `time-tracker (component)`, `password-vault (component)`, `task-view (component)`, `task-dashboard (component)`, `task-style (component)`, `taskapi (service)`, `passwordvaultapi (service)`, `TaskEmailClient (service)`
- Dependencies: apps: davvag-tools; schemas: profile; plugins: auth, phpcache, profile, sossdata; php-extensions: imap
- Dock subapps: `Projects`, `My Tasks`, `Time Tracker`
- Routes: `/ -> projects`, `/projects -> projects`, `/tasks -> tasks`, `/my-tasks -> my-tasks`, `/time-tracker -> time-tracker`, `/password-vault -> password-vault`, `/task -> task-view`, `/task-view -> task-view`
- Declared services: `taskapi`, `passwordvaultapi`, `TaskEmailClient`
- Registered service handlers:
  - passwordvaultapi (`services/passwordvaultapi/component.json`): Task Manager password vault service handlers; class PasswordVaultService; methods: ListVaults [POST], VaultDetails [POST], SaveVault [POST], DeleteVault [POST], CopyPassword [POST]
  - taskapi (`services/taskapi/component.json`): Task Manager service handlers; class TaskManagerService; methods: ListProjects [POST], ProjectDetails [POST], SaveProject [POST], DeleteProject [POST], ListTasks [POST], SaveTask [POST], DeleteTask [POST], ListMyTasks [POST], TaskDetails [POST], SaveWorkLog [POST], SaveComment [POST], ListProfiles [POST], SearchProfileByEmail [POST], ProjectAssignedProfiles [POST], NotifyTaskAssignees [POST]
  - TaskEmailClient (`services/TaskEmailClient/component.json`): Imports project mailbox messages into tasks and task discussion.; class TaskEmailClient; methods: getMail [GET] (projectId, search, limit, markSeen), Mail [GET] (projectId, search, limit, markSeen)
- PHP service files/methods: `services/passwordvaultapi/service.php: postListVaults, postVaultDetails, postSaveVault, postDeleteVault, postCopyPassword`; `services/taskapi/service.php: postListProfiles, postSearchProfileByEmail, postListProjects, postProjectDetails, postProjectAssignedProfiles, postSaveProject, postDeleteProject, postListTasks, postListMyTasks, postSaveTask, postDeleteTask, postTaskDetails, postSaveWorkLog, postSaveComment, postNotifyTaskAssignees`; `services/TaskEmailClient/service.php: getGetMail, getMail`

### tax-master - Tax Master

- Descriptor: `tax-master/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: system administrators
- Main features: Reference data management
- Startup component: tax-master-admin
- On-load components: tax-master-handler
- Component counts: component: 1, service: 1
- Components: `tax-master-admin (component)`, `tax-master-handler (service)`
- Dependencies: plugins: phpcache, sossdata
- Routes: `/ -> tax-master-admin`
- Declared services: `tax-master-handler`
- Registered service handlers:
  - tax-master-handler (`services/tax-master-handler/component.json`): Tax master service; class tax_master\TaxMasterService; methods: List [GET], Active [GET] (applyTo), Save [POST]
- PHP service files/methods: `services/tax-master-handler/service.php: getList, getActive, postSave`

### uicomponents - UI Components

- Descriptor: `uicomponents/app.json`
- Version/author: 0.1 / Davvag
- Tags: none
- Intended users: app users, admins
- Main features: Tagtextbox, Dependencies
- Startup component: none
- On-load components: none
- Component counts: service: 1, shell: 1
- Components: `tagtextbox (service)`, `dependencies (shell)`
- Dependencies: none
- Routes: none listed in descriptor
- Declared services: `tagtextbox`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### uom - UOM

- Descriptor: `uom/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: system administrators
- Main features: Reference data management
- Startup component: uom-all
- On-load components: uom-handler
- Component counts: service: 1, component: 2
- Components: `uom-handler (service)`, `uom-all (component)`, `uom-form (component)`
- Dependencies: apps: i18n
- Routes: `/uom -> uom-form`
- Declared services: `uom-handler`
- Registered service handlers: none found in component metadata
- PHP service files/methods: none found

### userapp - User App

- Descriptor: `userapp/app.json`
- Version/author: 0.71 / Davvag
- Tags: showincms
- Intended users: finance/payment admins, registered users
- Main features: Authentication and account pages, Chnage Password, User profile management, Payments, checkout, and finance workflows
- Startup component: login-switcher
- On-load components: login-handler
- Component counts: service: 1, component: 11
- Components: `login-handler (service)`, `login-form (component)`, `login-restpassword (component)`, `chnage-password (component)`, `login-switcher (component)`, `login-error (component)`, `frmprofile-view (component)`, `terms-conditions (component)`, `privacy-policy (component)`, `profile-edit (component)`, `frmInvoice-view (component)`, `frmRecipt-view (component)`
- Dependencies: apps: davvag-tools, i18n; schemas: ledger, orderheader_accepted, orderheader_pending, orderheader_rejected, payment_ext_request, profile, profile_attributes, profile_policy, profilestatus; plugins: Facebook, Google, auth, notify, phpcache, profile, sossdata
- Routes: `/login -> login-form`, `/profile -> frmprofile-view`, `/reset -> login-restpassword`, `/error -> login-error`, `/privacy-policy -> privacy-policy`, `/terms-conditions -> terms-conditions`, `/receipt -> frmRecipt-view`, `/invoice -> frmInvoice-view`
- Declared services: `login-handler`
- Registered service handlers:
  - product-handler (`services/login-handler/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), GoogleLogin [GET], FacebookLogin [GET], FacebookLoginCallback [GET], LoginState [GET], Logout [GET], getSession [GET] (token), resetPassword [GET] (email, token, password), ResetToken [GET] (email), registerUser [POST], ChangePassword [POST], updatePolicy [POST], ProfileData [GET], Save [POST], CreatePaymentRequest [POST]
- PHP service files/methods: `services/login-handler/service.php: postSave, getGoogleLogin, getLoginState, postCreatePaymentRequest, getProfileData, getFacebookLogin, getFacebookLoginCallback, postRegisterUser, postupdatePolicy, getGetSession, getLogin, getLogout, postChangePassword, getResetToken, getResetPassword`

### userapp_social - User App

- Descriptor: `userapp_social/app.json`
- Version/author: 0.1 / Davvag
- Tags: showindock
- Intended users: registered users
- Main features: Authentication and account pages, User profile management
- Startup component: login-switcher
- On-load components: login-handler
- Component counts: service: 1, component: 3
- Components: `login-handler (service)`, `login-form (component)`, `login-switcher (component)`, `frmprofile-view (component)`
- Dependencies: apps: i18n; schemas: profile; plugins: sossdata; php-extensions: curl
- Routes: `/login -> login-form`, `/profile -> frmprofile-view`
- Declared services: `login-handler`
- Registered service handlers:
  - product-handler (`services/login-handler/component.json`): Vue.JS Plugin for Soss Router; class LoginService; methods: login [GET] (email, password, domain), FacebookLogin [GET], FacebookLoginCallback [GET], LoginState [GET], logout [GET], getSession [GET] (token), resetPassword [GET] (email, token, password), getResetToken [GET] (email), registerUser [POST]
- PHP service files/methods: none found
