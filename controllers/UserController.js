angular.module("crud")
    .controller('UserCtrl', ['$scope', '$http', 'siteURL', function ($scope, $http, siteURL) {

        //Get all users
        $scope.users = {};
        $scope.orderPriority = "first_name";
        $scope.orderPrior = function(ordering){
            $scope.orderPriority = ordering;
            console.log($scope.orderPriority);
        };
        $http({
            'url': siteURL.url + 'lib/api.php',
            "method": 'get',
            "cache": true,
            "params": {action: 'get_users'}
        }).success(function (data) {
            $scope.users = data;
            console.log(data);
        });

    }]);