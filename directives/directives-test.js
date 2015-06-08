angular.module('crud')
    .directive('linkOrangeColor', function () {
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                element.css('color', 'orange');
                element.attr('target', '_blank');
            }
        }
    });