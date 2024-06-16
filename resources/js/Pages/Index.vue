<template>

    <div class="m-4 p-4">

        <div class="w-full flex justify-center">
            <h1 class="font-semibold text-xl">Speed Test Results</h1>
        </div>


        <div>
            <label for="range" class="block text-sm font-medium leading-6 text-gray-900">Range:</label>
            <select id="range"
                    v-model="range"
                    name="range"
                    class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                <option value="day">24hrs</option>
                <option value="week">Week</option>
                <option value="all">All time</option>
            </select>
        </div>

        <hourly-chart v-if="range === 'day'" :series="dayData.series">

        </hourly-chart>

        <weekly-chart v-if="range === 'week'" :series="weekData.series" :categories="weekData.categories">

        </weekly-chart>

        <all-time-chart v-if="range === 'all'" :series="allData.series" :categories="allData.categories">

        </all-time-chart>

    </div>

</template>

<script setup>
import HourlyChart from "@/Components/HourlyChart.vue";
import WeeklyChart from "@/Components/WeeklyChart.vue";
import AllTimeChart from "@/Components/AllTimeChart.vue";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const range = ref('day');

const props = defineProps({
    dayData: Object,
    weekData: Object,
    allData: Object,
});

setTimeout(() => {
    router.visit('/', {
        only: ['dayData', 'weekData', 'allData'],
        preserveState: true,
        preserveScroll: true,
    });
}, 5000);

</script>


