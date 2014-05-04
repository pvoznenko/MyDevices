My Devices Symfony 2 Application
================================

Demo Symfony2 project guided by specified goal, tools, steps and criteria.

How to Run This Project
-----------------------

Use [Composer][13] to update vendor libraries:

    $ php composer.phar install

If you do not have [Composer][13] you can install it in your project:

    $ curl -sS https://getcomposer.org/installer | php

Before running Symfony2 for the first time, execute the following command to make sure that your system meets all the
technical requirements:

    $ php app/check.php

More information about installation you can find in Symfony2 docs: [http://symfony.com/doc/current/quick_tour/the_big_picture.html][14]

Then we need to have all our vendor JS libraries. To manage packages app using [Bower][11]. Run command:

    $ bower install

Then we need install our assets:

    $ php app/console assetic:dump

And publish assets for [FOSJsRoutingBundle][15]:

    $ php app/console assets:install --symlink web

To install DB scheme use command:

    $ php app/console doctrine:schema:create

Make sure you have correct config for db at file: `app/config/parameters.yml`

To run functional test with [PHPUnit][17] use command:

    $ phpunit -c app/phpunit.xml.dist

Technical Information
---------------------

### Frontend

  * [Twitter Bootsrap 3][1] been used for handle responsive design;

  * Device list with pagination and sorting - [Datatable][2] with [Bootstrap 3 plugin][3];

  * To visual information about devices [Highcharts library][4] been used. Since it is not commercial the project
    library can be used for free;

  * To get unique device indicator I used js library ['fingerprint.js'][5] that implements fingerprinting technique - it
    is described in the [research by Electronic Frontier Foundation][6];

  * Device information collected by ['Browser.js'][7] library;

  * Since on the whole frontend there was only few element selection, in scope of current task I did not see a reason
    (since task was not implemented as single page REST application) of using any MVC \ MVP js libraries like [AngularJS][8]
    or [Backbone.js][9]. Libraries that been used in frontend depends on [jQuery][10] library;

  * For managing JS dependencies in Bundle, package manager [Bower][11] was used. Information about libraries and
    versions you can find in Bundle `bower.json`;

  * During development of application I needed to create custom js module ‘NewDeviceManager'
    (`Devices/MyDevicesBundle/Resources/public/js/new-device-manager.js`) to handle checking and adding user's new devices.
    This is the only place where I could use [Backbone.js][9] to handle action in index view to structure code, but I am not
    sure about the efficiency of using the whole library just to handle two requests, since it is made as it is. I know that if
    this project went live, for future development to have structure code (to avoid spaghetti monster) it preferable to
    use some MVC / MVP JS libraries. So in current task (scope) I prefer [YAGNI][12] principle (You aren't gonna need it);

  * Second custom js module is 'PieChartManager' (`Devices/MyDevicesBundle/Resources/public/js/pie-chart-manager.js`).
    Used for asynchronous ajax data loading and rendering for [Highcharts][4] Pie Chart for admin user for device statistics.

### Backend

  * For current task all logic implemented in MyDevicesBundle;

  * For Registration and Login application using [FOSUserBundle][16]. Templates for Login and Registration was override in
    MyDevicesBundle, so it use overall responsive templates. There is no page for user profile and account deletion;

  * Application access control specified in config `security.yml`;

  * MyDevicesBundle contains functional tests;

  * For handle application URLs in javascript [FOSJsRoutingBundle][15] used;

  * There are two DB tables ‘`user`' and ‘`device`’ with one-to-many connection and on cascade remove. Also for current
    task I did not see a reason to do over-engineering with DB structure, like implementing Dictionary for device’s attributes
    where we could store as much as we wanted for any kind of device.

Scenarios
---------

### User

  * A user can sign up or log in to the application to see a paginated table of his devices with short information ordered
    by latest;

  * If user logged in from new device which was not assigned before, modal dialog will appear with proposal to add new
    device to user’s device list;

  * If user accepts, the device is saved and the user is redirected to a page showing information about new device;

  * In device view user can edit or delete device;

  * To not make unnecessary calls to the backend, application store cookie about device fingerprint. If cookie not set,
    application will check if current device was already assigned to the user. Cookie cleans on login;

### Admin User

  * On main page admin see list of all user’s devices in the system;

  * Admin can edit and delete all devices in the system;

  * Application is not offering administrator to add his device;

  * Admin have device Statistics page, where he can see pie charts with device information stored in the system.

**IMPORTANT:** To use admin rights, please create user, for example through console using fos command:

    $ php app/console fos:user:create

and promote user with ‘`ROLE_ADMIN`’:

    $ php app/console fos:user:promote

Then you can login with this user to the system as administrator.

[1]: http://getbootstrap.com/
[2]: https://datatables.net/
[3]: https://github.com/Jowin/Datatables-Bootstrap3
[4]: http://www.highcharts.com/
[5]: https://github.com/Valve/fingerprintjs
[6]: https://panopticlick.eff.org/browser-uniqueness.pdf
[7]: https://github.com/thorst/Browser
[8]: https://angularjs.org/
[9]: http://backbonejs.org/
[10]: http://jquery.com/
[11]: https://github.com/bower/bower
[12]: http://en.wikipedia.org/wiki/You_aren't_gonna_need_it
[13]: https://getcomposer.org/
[14]: http://symfony.com/doc/current/quick_tour/the_big_picture.html
[15]: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
[16]: https://github.com/FriendsOfSymfony/FOSUserBundle
[17]: http://phpunit.de/
