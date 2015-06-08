angular.module("crud")
    .controller('HomeCtrl', ['$scope', '$http', 'siteURL', function ($scope, $http, siteURL) {
        $scope.crud_search = '';
        $scope.autocomplete_data = {};
        $scope.searchData = function () {
            if ($scope.crud_search != "") {
                $http.get(siteURL.url + 'lib/api.php', {
                    params: {
                        action: 'get_autocomplete_data',
                        term: $scope.crud_search
                    }
                })
                    .success(function (data) {
                        $scope.autocomplete_data = data;
                    });
            }
            else{
                $scope.autocomplete_data = false;
            }

        }
    }])
;