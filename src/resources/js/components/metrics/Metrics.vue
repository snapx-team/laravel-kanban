<template>
    <div>
        <div class="flex flex-wrap p-4 pl-10">
            <h3 class="text-3xl text-gray-800 font-bold py-1 pr-2">Metrics - </h3>
            <h3 class="text-3xl text-gray-800 py-1"> Between {{ start | moment("MMM Do YYYY") }} and
                {{ end | moment("MMM Do YYYY") }}</h3>
        </div>
        <div class="bg-gray-100 w-full h-64 absolute top-0 rounded-b-lg" style="z-index: -1"></div>
        <div class="mx-10 my-3 space-y-5 shadow-xl p-5 bg-white">
            <div class="flex-1 space-y-2">
                <span
                    class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Start</span>
                <date-picker type="date" v-model="start"
                             placeholder="YYYY-MM-DD"
                             format="YYYY-MM-DD"
                ></date-picker>
                <div class="flex-1 space-y-2">
                    <span
                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">End</span>
                    <date-picker type="date" v-model="end"
                                 placeholder="YYYY-MM-DD"
                                 format="YYYY-MM-DD">
                    </date-picker>
                </div>
            </div>

            <button @click="filter()"
                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                    type="button">
                <span>Filter</span>
            </button>
        </div>

        <div class="p-10">

            <div class="grid grid-cols-12 gap-4">

                <div class="lg:col-span-4 col-span-12">
                    <div @click="showTaskBreakdown()"
                         :class="{'bg-gray-200': showTB}"
                         class="flex items-center px-3 rounded my-2 border hover:bg-gray-200 py-2 cursor-pointer">
                        <span class="font-medium text-gray-900 pl-2">Task Breakdown</span>
                    </div>

                    <div @click="showEmployeeBreakdown()"
                         :class="{'bg-gray-200': shoeEB}"
                         class="flex items-center px-3 rounded my-2 border hover:bg-gray-200 py-2 cursor-pointer">
                        <span class="font-medium text-gray-900 pl-2">Employee Breakdown</span>
                    </div>

                    <div @click="showPeakHours()"
                         :class="{'bg-gray-200': shoePH}"
                         class="flex items-center px-3 rounded my-2 border hover:bg-gray-200 py-2 cursor-pointer">
                        <span class="font-medium text-gray-900 pl-2">Peak Hours Metrics</span>
                    </div>

                    <div @click="showTimePerformance()"
                         :class="{'bg-gray-200': shoeTP}"
                         class="flex items-center px-3 rounded my-2 border hover:bg-gray-200 py-2 cursor-pointer">
                        <span class="font-medium text-gray-900 pl-2">Time Performance</span>
                    </div>

                    <div @click="showCreatedVsResolved()"
                         :class="{'bg-gray-200': showCR}"
                         class="flex items-center px-3 rounded my-2 border hover:bg-gray-200 py-2 cursor-pointer">
                        <span class="font-medium text-gray-900 pl-2">Created Vs Resolved</span>
                    </div>
                </div>

                <div class="lg:col-span-8 col-span-12">
                    <div v-if="chartIsLoading"
                         class="m-2 rounded bg-blue-50 flex flex-col items-center bg-blue-50 h-full">
                        <loading-animation :size="80" class="m-auto"></loading-animation>
                    </div>
                    <div v-else>
                        <div v-if="showTB">
                            <pie-chart
                                class="border rounded my-2 max-w-screen-sm"
                                :data="badgeData.data"
                                :errors="badgeData.errors"
                                :title="'Tasks created: grouped by badge'">
                            </pie-chart>

                            <pie-chart
                                class="border rounded my-2 max-w-screen-sm"
                                :series="contractData.data.hits"
                                :labels="contractData.data.names"
                                :errors="contractData.errors"
                                :title="'Tasks created: grouped by contracts'">
                            </pie-chart>
                        </div>
                        <div v-if="shoeEB">
                            <pie-chart
                                class="border rounded my-2 max-w-screen-sm"
                                :series="tasksCreatedData.data.hits"
                                :labels="tasksCreatedData.data.names"
                                :errors="tasksCreatedData.errors"
                                :title="'Tasks created by employee'">
                            </pie-chart>

                            <pie-chart
                                class="border rounded my-2 max-w-screen-sm"
                                :series="closedTasksByAssignedTo.data.hits"
                                :labels="closedTasksByAssignedTo.data.names"
                                :errors="closedTasksByAssignedTo.errors"
                                :title="'Closed tasks per assigned employees'">
                            </pie-chart>

                            <pie-chart
                                class="border rounded my-2 max-w-screen-sm"
                                :series="closedTasksByAdmin.data.hits"
                                :labels="closedTasksByAdmin.data.names"
                                :errors="closedTasksByAdmin.errors"
                                :title="'Tasks Closed by Admin'">
                            </pie-chart>
                        </div>
                        <div v-if="shoePH" class="border rounded my-2 ">
                            <bar-chart
                                :data="peakHoursTasksCreated.data.hits"
                                :categories="peakHoursTasksCreated.data.names"
                                :errors="peakHoursTasksCreated.errors"
                                :xname="'Tasks'"
                                :yname="'Hour'"
                                :title="'Tasks Created'">
                            </bar-chart>
                        </div>
                        <div v-if="shoeTP">

                            <bar-chart
                                class="border rounded my-2"
                                :data="estimatedHoursCompletedByEmployees.data.hits"
                                :categories="estimatedHoursCompletedByEmployees.data.names"
                                :errors="estimatedHoursCompletedByEmployees.errors"
                                :xname="'Hours'"
                                :yname="'Employees'"
                                :title="'Estimated hours completed'">
                            </bar-chart>

                            <bar-chart
                                class="border rounded my-2"
                                :data="averageHoursToCompletionByEmployee.data.hits"
                                :categories="averageHoursToCompletionByEmployee.data.names"
                                :errors="averageHoursToCompletionByEmployee.errors"
                                :xname="'Hours'"
                                :yname="'Employees'"
                                :title="'Average hours from assigned to completed: By employee'">
                            </bar-chart>

                            <bar-chart
                                class="border rounded my-2"
                                :data="averageHoursToCompletionByBadge.data.hits"
                                :categories="averageHoursToCompletionByBadge.data.names"
                                :errors="averageHoursToCompletionByBadge.errors"
                                :xname="'Hours'"
                                :yname="'Categories'"
                                :title="'Average hours from assigned to completed: By badge'">
                            </bar-chart>

                        </div>
                        <div v-if="showCR" class="border rounded my-2 ">
                            <line-chart
                                :series="createdVsResolved.data.series"
                                :categories="createdVsResolved.data.categories"
                                :errors="createdVsResolved.errors"
                                :xname="'Days'"
                                :yname="'Total Tasks'"
                                :title="'Created Vs Resolved'">
                            </line-chart>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import PieChart from "./metricsComponents/PieChart.vue";
import BarChart from "./metricsComponents/BarChart.vue";
import LineChart from "./metricsComponents/LineChart.vue";
import BacklogBar from "../backlog/backlogComponents/BacklogBar.vue";
import LoadingAnimation from "../global/LoadingAnimation";

export default {
    inject: ["eventHub"],
    components: {
        PieChart,
        BarChart,
        LineChart,
        BacklogBar,
        LoadingAnimation
    },
    data() {
        return {
            chartIsLoading: false,
            start: this.getMonday(new Date()),
            end: new Date(),
            showTB: true,
            shoeEB: false,
            shoePH: false,
            shoeTP: false,
            showCR: false,
            //pie chart data
            badgeData: {data: {hits: [], names: []}, errors: []},
            tasksCreatedData: {data: {hits: [], names: []}, errors: []},
            contractData: {data: {hits: [], names: []}, errors: []},
            closedTasksByAssignedTo: {data: {hits: [], names: []}, errors: []},
            closedTasksByAdmin: {data: {hits: [], names: []}, errors: []},
            //bar chart data
            peakHoursTasksCreated: {data: {hits: [], names: []}, errors: []},
            estimatedHoursCompletedByEmployees: {data: {hits: [], names: []}, errors: []},
            averageHoursToCompletionByBadge: {data: {hits: [], names: []}, errors: []},
            averageHoursToCompletionByEmployee: {data: {hits: [], names: []}, errors: []},
            //line chart data
            createdVsResolved: {data: {series: [], categories: []}, errors: []},
        }
    },

    mixins: [ajaxCalls],

    mounted() {
        this.start.setDate(this.start.getDate() - (this.start.getDay() + 6) % 7);
        this.reload();
    },
    computed: {
        startTime() {
            return this.start.toISOString().slice(0, 10);
        },
        endTime() {
            return this.end.toISOString().slice(0, 10);
        }
    },
    methods: {
        reload() {
            if (this.showTB) {
                this.showTaskBreakdown();
            }
            if (this.shoeEB) {
                this.showEmployeeBreakdown()
            }
            if (this.shoePH) {
                this.showPeakHours();
            }
            if (this.shoeTP) {
                this.showTimePerformance()
            }
            if (this.showCR) {
                this.showCreatedVsResolved();
            }

        },
        showTaskBreakdown() {
            this.chartIsLoading = true;
            this.showTB = true;
            this.shoeEB = false;
            this.shoePH = false;
            this.shoeTP = false;
            this.showCR = false;

            Promise.all([this.getBadgeData(), this.getContractData()]).then(() => {
                this.chartIsLoading = false;
            }).catch(() => {
                this.chartIsLoading = false;
                this.triggerErrorToast('Invalid Data Entered');
            });
        },
        showEmployeeBreakdown() {
            this.chartIsLoading = true;
            this.showTB = false;
            this.shoeEB = true;
            this.shoePH = false;
            this.shoeTP = false;
            this.showCR = false;

            Promise.all([this.getTasksCreatedByEmployee(), this.getClosedTasksByAssignedTo(), this.getClosedTasksByAdmin()]).then(() => {
                this.chartIsLoading = false;
            }).catch(() => {
                this.chartIsLoading = false;
                this.triggerErrorToast('Invalid Data Entered');
            });
        },
        showPeakHours() {
            this.chartIsLoading = true;
            this.showTB = false;
            this.shoeEB = false;
            this.shoePH = true;
            this.shoeTP = false;
            this.showCR = false;

            Promise.all([this.getPeakHoursTasksCreated()]).then(() => {
                this.chartIsLoading = false;
            }).catch(() => {
                this.chartIsLoading = false;
                this.triggerErrorToast('Invalid Data Entered');
            });
        },
        showTimePerformance() {
            this.chartIsLoading = true;
            this.showTB = false;
            this.shoeEB = false;
            this.shoePH = false;
            this.shoeTP = true;
            this.showCR = false;

            Promise.all([this.getEstimatedHoursCompletedByEmployees(), this.getAverageTimeToCompletionByBadge(), this.getAverageTimeToCompletionByEmployee()]).then(() => {
                this.chartIsLoading = false;
            }).catch(() => {
                this.chartIsLoading = false;
                this.triggerErrorToast('Error loading metrics');
            });
        },
        showCreatedVsResolved() {
            this.chartIsLoading = true;
            this.showTB = false;
            this.shoeEB = false;
            this.shoePH = false;
            this.shoeTP = false;
            this.showCR = true;

            Promise.all([this.getCreatedVsResolved()]).then(() => {
                this.chartIsLoading = false;
            }).catch(() => {
                this.chartIsLoading = false;
                this.triggerErrorToast('Invalid Data Entered');
            });
        },
        getMonday(d) {
            d = new Date(d);
            let day = d.getDay(),
                diff = d.getDate() - day + (day === 0 ? -6 : 1); // adjust when day is sunday
            return new Date(d.setDate(diff));
        },
        filter() {
            if (this.start === null || this.start === '' || this.end === null || this.end === '') {
                this.triggerErrorToast("fill date fields");
            } else {
                this.reload();
            }
        },

        // all metric calls

        async getBadgeData() {
            return await this.asyncGetBadgeData(this.startTime, this.endTime).then((data) => {
                this.badgeData.data = data.data.data;
                this.badgeData.errors = [];
            }).catch((error) => {
                this.badgeData.errors = Object.values(error.response.data.errors).flat();
                throw new Error('getBadgeData failed')
            })
        },
        async getContractData() {
            return await this.asyncGetContractData(this.startTime, this.endTime).then((data) => {
                this.contractData.data = data.data.data;
                this.contractData.errors = [];
            }).catch((error) => {
                this.contractData.errors = Object.values(error.response.data.errors).flat();
                throw new Error('getContractData failed')
            })
        },
        async getTasksCreatedByEmployee() {
            return await this.asyncGetTasksCreatedByEmployee(this.startTime, this.endTime).then((data) => {
                this.tasksCreatedData.data = data.data.data;
                this.tasksCreatedData.errors = [];
            }).catch((error) => {
                this.tasksCreatedData.errors = Object.values(error.response.data.errors).flat();
                throw new Error('getTasksCreatedByEmployee failed')
            })
        },
        async getEstimatedHoursCompletedByEmployees() {
            return await this.asyncGetEstimatedHoursCompletedByEmployees(this.startTime, this.endTime).then((data) => {
                this.estimatedHoursCompletedByEmployees.data = data.data.data;
                this.estimatedHoursCompletedByEmployees.errors = [];
            }).catch((error) => {
                this.estimatedHoursCompletedByEmployees.errors = Object.values(error.response.data.errors).flat();
                throw new Error('getEstimatedHoursCompletedByEmployees failed')
            })
        },
        async getPeakHoursTasksCreated() {
            return await this.asyncGetPeakHoursTasksCreated(this.startTime, this.endTime).then((data) => {
                this.peakHoursTasksCreated.data = data.data.data;
                this.peakHoursTasksCreated.errors = [];
            }).catch((error) => {
                this.peakHoursTasksCreated.errors = Object.values(error.response.data.errors).flat();
                throw new Error('getPeakHoursTasksCreated failed')
            })
        },
        async getClosedTasksByAssignedTo() {
            return await this.asyncGetClosedTasksByAssignedTo(this.startTime, this.endTime).then((data) => {
                this.closedTasksByAssignedTo.data = data.data.data;
                this.closedTasksByAssignedTo.errors = [];
            }).catch((error) => {
                this.closedTasksByAssignedTo.errors = Object.values(error.response.data.errors).flat();
                throw new Error('getClosedTasksByAssignedTo failed')
            })
        },
        async getClosedTasksByAdmin() {
            return await this.asyncGetClosedTasksByAdmin(this.startTime, this.endTime).then((data) => {
                this.closedTasksByAdmin.data = data.data.data;
                this.closedTasksByAdmin.errors = [];
            }).catch((error) => {
                this.closedTasksByAdmin.errors = Object.values(error.response.data.errors).flat();
                throw new Error('getClosedTasksByAdmin failed')
            })
        },
        async getAverageTimeToCompletionByBadge() {
            return await this.asyncGetAverageTimeToCompletionByBadge(this.startTime, this.endTime).then((data) => {
                this.averageHoursToCompletionByBadge.data = data.data.data;
                this.averageHoursToCompletionByBadge.errors = [];
            }).catch((error) => {
                this.averageHoursToCompletionByBadge.errors = Object.values(error.response.data.errors).flat();
                throw new Error('getAverageTimeToCompletionByBadge failed')
            })
        },
        async getAverageTimeToCompletionByEmployee() {
            return await this.asyncGetAverageTimeToCompletionByEmployee(this.startTime, this.endTime).then((data) => {
                this.averageHoursToCompletionByEmployee.data = data.data.data;
                this.averageHoursToCompletionByEmployee.errors = [];
            }).catch((error) => {
                this.averageHoursToCompletionByEmployee.errors = Object.values(error.response.data.errors).flat();
                throw new Error('getAverageTimeToCompletionByEmployee failed')
            })
        },
        async getCreatedVsResolved() {
            return await this.asyncGetCreatedVsResolved(this.startTime, this.endTime).then((data) => {
                this.createdVsResolved.data = data.data.data;
                this.createdVsResolved.errors = [];
            }).catch((error) => {
                this.createdVsResolved.errors = Object.values(error.response.data.errors).flat();
                throw new Error('getCreatedVsResolved failed')
            })
        }
    }
}
</script>
