<template>

    <div id="chart" class="mt-4 p-4 bg-white rounded-lg shadow">
        <apexchart type="area" height="350" :options="chartOptions" :series="series">

        </apexchart>
    </div>

</template>
<script setup>

const props = defineProps({
    series: Array,
    categories: Array,
});

const chartOptions = {
    xaxis: {
        categories: props.categories
    },
    stroke: {
        curve: 'smooth'
    },
    tooltip: {
        y: {
            formatter: function (value) {
                return value?.toFixed(2); // Rounds the value to 2 decimal places
            }
        }
    },
    dataLabels: {
        enabled: false,
    },
    markers: {
        size: 0,
    },
    legend: {
        markers: {
        }
    },
};

</script>
