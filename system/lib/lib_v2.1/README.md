isoTope - a php micro-framework

This is the core back end library for a framework/CMS which has been in use in various forms for 15 years.

It is important to note that this is based on legacy code, and is in the process of being converted to a proper OO format, and to run as an api as well, however is a pet project and not a commercian interest.

Originally written in Perl circa 1999/2000, it was re-written in PHP 2007/2008. OO Implementation started 2011, interstetially as static functions before being moved to a proper OO framework. It is currently in a hybrid state.


Purpose
-------

The original purpose of this project was to provide a content management system - similar to Joomla, Wordpress, etc. with framework and database management functionality.
The motivation was Wordpress and other CMS`s inability to natively deal with custom format data (custom data tables and relationships, etc), also to stop repetitive programming (writing add/update/view functionality into numerous custom database tables where one piece of code could suffice).


Features 
--------

* Full templating - *everything* - page templates, recordset templates inc. paging and filtering, form templates, record templates, individual field templates. All are nested hierarchically so design can be completely separated from content. Eg. A form will consist of field templates within a form template within a content page template within a site template. Unique classes and ids automatically applied to everything for custom styling in the front end.

* A custom templating language which can be used in content/templates/widgets to pick up database field data, request variables, global variables, cookies etc, and pass them into forms, recordsets, sql etc. Includes logic parsing (if [condition] and [second condition] or [NOT third condition] etc) which can be written directly into content/widgets/templates. Templates can reach out to custom PHP scripts, SQL queries, etc around this language making it very dynamic and flexible. 

* Insert forms, records and recordsets anywhere with fully configurable options, covering amongst other things, <select> list sources (database queries, static lists, directories), linked <select> elements where one option being selected decides source lists of others, custom input types (image pickers, dates),  automatically linking to other tables on foreign keys to find correct values as pre pre-defined rules in both forms and data views, different functionality or filters per user type, etc) 

* Ability to add SQL directly into content or templates or widgets. This SQL is parsed via a custom SQL parser with full permissions checking for security. Not every feature of SQL is supported but sub-selects and aggregate queries inc. HAVING clauses are, along with cross-database queries. All tables appearing in SQL queries are checked for CRUD access permissions as the SQL is parsed. 

* Permissions applied to tables globally or to individual records. Individual permissions for view record(s), add record, update record, delete record, table drop and create can be applied to users or groups, and can work in a hierarchial fashion depending on the user types you set up. 

* User login/logout/password change / forgotten login via token system - also fully templatable

* Dynamic multi-window (draggable/resizable/minimize/maximize etc) interface (packaged separately to this repository) written in Mootools

* Visual drag/drop database schema design/view

* Shopping cart software included, featuring (amongst other things) different prices per user type, various shipping modules (per quantity, per quantity/cart category, flat rates, by weights), checkout itemisation modules, optional VAT itemisation, discount and promo codes, complex gift vouchers) with hooks built in for writing custom functionality and connecting to third party sources for placing orders and stock control (eg. fulfillment company APIs)



Folder structure
----------------

classes - 
 - core - core classes such as database class, form, reconrdset, template classes, codeparser template language parser etc
 - search - custom search functionality classes
 - shopping cart - all shopping cart classes, inc further folders for shipping modules, payment modules etc 
controllers - as part of the in-progress conversion to an api, controllers are called by the routing scripts. 
interfaces - any interfaces used go in here 
library - libraries of functions, organised by type and what they are to do with (tables, queries, filters etc). Many of these will become models and related to the various controllers. 
routing - routing for main site and admin
