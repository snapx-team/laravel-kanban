<template>
    <div>
        <div v-if="this.series.length > 0" id="chart">
            <vue-apex-charts :options="computedChartOptions"
                             :series="this.series"></vue-apex-charts>
        </div>
        <div class="text-lg font-light leading-relaxed p-3" v-if="this.series.length === 0">
            No Data For <b>{{ this.title }}</b>
            <p class="text-red-600 font-semibold text-sm pt-2" v-for="error in errors">{{ error }}</p>
        </div>
    </div>
</template>

<script>
import VueApexCharts from 'vue-apexcharts';

export default {
    inject: ["eventHub"],
    components: {
        VueApexCharts,
    },
    data() {
        return {
            chartOptions: {
                chart: {
                    type: 'pie',
                },
                labels: [],
                legend: {
                    offsetY: 40,
                    width: 300,
                },
                responsive: [{
                    breakpoint: 1300,
                    options: {
                        legend: {
                            width: 200,
                        },
                    },
                }]
            },
        }
    },
    props: {
        series: {
            default: Array,
        },
        labels: {
            default: Array,
        },
        title: {
            default: '',
        },
        errors: {
            default: Array
        }
    },
    computed: {
        computedChartOptions() {
            return {
                ...this.chartOptions, ...{
                    labels: this.labels,
                    title: {
                        text: this.title,
                        align: 'center',
                        margin: 40,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            fontFamily: undefined,
                            color: '#263238'
                        },
                    },
                }
            };
        }
    }
}
</script>
