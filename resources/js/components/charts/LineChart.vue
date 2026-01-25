<script setup lang="ts">
import { merge } from 'lodash';
import { computed } from 'vue';

const props = defineProps<{
    options: object;
}>();

const baseOptions = {
    accessibility: { enabled: false },
    chart: { height: 250 },
    legend: {
        align: 'right',
        verticalAlign: 'top',
    },
    plotOptions: {
        series: {
            marker: { enabled: false },
        },
    },
    title: { text: null },
    xAxis: {
        dateTimeLabelFormats: {
            day: '%d. %m.',
            week: '%d. %m.',
        },
        lineColor: '#CCD6EB',
        tickColor: '#CCD6EB',
        type: 'datetime',
    },
    yAxis: {
        minTickInterval: 1,
        title: { enabled: false },
    },
};

const finalOptions = computed(() => {
    return merge({}, baseOptions, props.options);
});
</script>

<template>
    <highcharts :options="finalOptions"></highcharts>
</template>
