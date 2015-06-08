(function () {
    var app = angular.module('crud', ['ngRoute', 'ngAnimate']);
    app.constant('siteURL', {
        url: 'http://server-2015/ANGULAR/crud/'
    });
    app.run(function () {
    });
    app.config(['$locationProvider', '$routeProvider', function ($locationProvider, $routeProvider) {
        $routeProvider
            .when('/', {
                templateUrl: 'templates/home.html',
                controller: 'HomeCtrl'
            })
            .when('/save-data', {
                templateUrl: 'templates/save-issue.html',
                controller: 'SaveCtrl'
            })
            .when('/users', {
                templateUrl: 'templates/users.html',
                controller: 'UserCtrl'
            })
            .when('/add-user', {
                templateUrl: 'templates/add-user.html',
                controller: 'UserCtrl'
            })
            .when('/directives-example', {
                templateUrl : 'templates/directives.html',
                controller : 'DirctCtrl'
            })
            .otherwise({redirectTo: '/'})
    }]);
}());