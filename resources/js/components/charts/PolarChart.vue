<script setup lang="ts">
import { merge } from 'lodash';
import { computed } from 'vue';

const props = defineProps<{
    options: object;
}>();

const baseOptions = {
    accessibility: { enabled: false },
    chart: {
        height: 250,
        polar: true,
        type: 'line',
    },
    legend: { enabled: false },
    pane: { size: '65 %' },
    plotOptions: {
        series: {
            marker: { enabled: false },
        },
    },
    title: { text: null },
    tooltip: {
        pointFormat: '<b>{point.y:,.0f} %</b>',
    },
    xAxis: {
        lineWidth: 0,
        tickmarkPlacement: 'on',
        labels: {
            distance: 20,
            formatter: function (): string | undefined {
                if (this.pos >= this.axis.categories.length) {
                    return;
                }

                const val = this.chart.series[0].dataTable.columns.y[this.pos];
                return `<div class="text-center">${this.value}<br/><b>${val} %</b></div>`;
            },
            useHTML: true,
        },
    },
    yAxis: {
        gridLineInterpolation: 'polygon',
        gridLineWidth: 0,
        labels: { enabled: false },
        min: 0,
    },
};

const finalOptions = computed(() => {
    return merge({}, baseOptions, props.options);
});
</script>

<template>
    <highcharts :options="finalOptions"></highcharts>
</template>
