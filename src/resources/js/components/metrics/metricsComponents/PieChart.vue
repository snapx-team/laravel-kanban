<template>
    <div id="chart">
        <vue-apex-charts type="pie" width="380" :options="computedChartOptions" :series="this.series"></vue-apex-charts>
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
                labels: ['1', '2', '3'],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            },
        }
    },
    props: {
        series: {
            default: [],
        },
        labels: {
            default: [],
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
                            fontSize:  '14px',
                            fontWeight:  'bold',
                            fontFamily:  undefined,
                            color:  '#263238'
                        }, 
                    },
                }
            };
        }
    }
}
</script>
