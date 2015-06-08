angular.module('crud')
    .directive('rpAutocomplete', function () {
        return {
            templateUrl: './directives/autocomplete.html',
            restrict: 'E',
            scope: {
                data: '='
            }
        }

    });