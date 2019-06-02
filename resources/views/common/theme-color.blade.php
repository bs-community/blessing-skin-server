@php
$colors = [
    'blue' => '#3c8dbc',
    'yellow' => '#f39c12',
    'green' => '#00a65a',
    'purple' => '#605ca8',
    'red' => '#dd4b39',
    'black' => '#ffffff',
];
preg_match('/skin-(\w+)?(?:-light)?/', option('color_scheme'), $matches);
@endphp

<meta name="theme-color" content="{{ $colors[$matches[1]] }}">
