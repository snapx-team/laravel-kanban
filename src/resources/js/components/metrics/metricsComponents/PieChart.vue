<template>
    <div>
        <div v-if="this.series.length > 0" id="chart">
            <vue-apex-charts type="pie" width="400" height="400" :options="computedChartOptions"
                             :series="this.series"></vue-apex-charts>
        </div>
        <div v-if="this.series.length === 0">
            No Data for {{ this.title }}
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
                    width: 380,
                    type: 'pie',
                },
                labels: [],
                legend: {
                    formatter: function (val) {
                        const n = 12
                        if (val) {
                            return val.length > n ? val.substr(0, n - 1) + '...' : val
                        }
                        return val;
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom',
                        }
                    }
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
                        margin: 10,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '14px',
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
