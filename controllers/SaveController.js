angular.module("crud")
    .controller('SaveCtrl', ['$scope', '$http', '$timeout', 'siteURL', function ($scope, $http, $timeout, siteURL) {
        $scope.categories = {};
        $scope.showSuccess = false;
        $scope.formData = {};
        $scope.formData.issue_title = '';
        $scope.formData.issue_category = '';
        $scope.formData.issue_link = '';
        $scope.formData.issue_description = '';
        $scope.formData.issue_code = '';
        // Get categories
        $http.get('data/prog-langs.json').success(function (data) {
            $scope.categories = data;
        });

        //Save data
        $scope.saveData = function () {
            $scope.showSuccess = false;
            $http({
                'url': siteURL.url + 'lib/api.php',
                'method': 'POST',
                data: {action: "save_data", form_data: $scope.formData},
                headers: {
                    "Accept": "application/json;charset=utf-8"
                }

            }).success(function (data) {
                if (parseInt(data) > 0) {
                    $scope.showSuccess = true;
                    $scope.formData.issue_title = '';
                    $scope.formData.issue_category = '';
                    $scope.formData.issue_link = '';
                    $scope.formData.issue_description = '';
                    $scope.formData.issue_code = '';

                    $timeout(function () {
                        $scope.showSuccess = false;
                    }, 2000);
                }
            });
        };
    }]);