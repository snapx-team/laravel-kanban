<template>
    <div>
        <div class="bg-gray-100 w-full h-64 absolute top-0 rounded-b-lg" style="z-index: -1"></div>

        <div class="mx-10 my-3 space-y-5 shadow-xl p-5 bg-white">
            <div class="bg-gray-100 py-3">

                <div class="p-2 flex flex-wrap">
                    <div class="flex-auto rounded shadow-lg m-2 bg-white">
                        <pie-chart v-if="badgeData != null" :series="badgeData.hits" :labels="badgeData.names" :title="'Tickets by Badge'"></pie-chart>
                    </div>
                    <div class="flex-auto rounded shadow-lg m-2 bg-white">
                        <pie-chart v-if="ticketByEmployeeData != null" :series="ticketByEmployeeData.hits" :labels="ticketByEmployeeData.names" :title="'Tickets Created by Employee'"></pie-chart>
                    </div>
                    <div class="flex-auto rounded shadow-lg m-2 bg-white">
                        <pie-chart v-if="jobsiteData != null" :series="jobsiteData.hits" :labels="jobsiteData.names" :title="'Tickets by JobSite'"></pie-chart>
                    </div>
                    <div class="flex-auto rounded shadow-lg m-2 bg-white">
                        <pie-chart v-if="closedTasksByEmployee != null" :series="closedTasksByEmployee.hits" :labels="closedTasksByEmployee.names" :title="'Tickets Closed by Employee'"></pie-chart>
                    </div>
                    <div class="flex-auto rounded shadow-lg m-2 bg-white">
                        <bar-chart  v-if="creationByHour != null" :data="creationByHour.hits" :categories="creationByHour.names" :xname="'Tickets'" :yname="'Hour'" :title="'Tickets by Hour Created'"></bar-chart>
                    </div>
                    <div class="flex-auto rounded shadow-lg m-2 bg-white">
                        <bar-chart  v-if="delayByBadge != null" :data="delayByBadge.hits" :categories="delayByBadge.names" :xname="'Hours'" :yname="'Categories'" :title="'Avg Hrs by Badge (open)'"></bar-chart>
                    </div>
                    <div class="flex-auto rounded shadow-lg m-2 bg-white">
                        <bar-chart  v-if="delayByEmployee != null" :data="delayByEmployee.hits" :categories="delayByEmployee.names" :xname="'Hours'" :yname="'Employees'" :title="'Avg Hrs by Employee (assigned)'"></bar-chart>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>  
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import PieChart from  "./metricsComponents/PieChart.vue";
import BarChart from  "./metricsComponents/BarChart.vue";

export default {
    inject: ["eventHub"],
    components: {
        PieChart,
        BarChart,
    },
    data() {
        return {
            badgeData: null,
            ticketByEmployeeData: null,
            creationByHour: null,    
            jobsiteData: null,
            closedTasksByEmployee: null,
            delayByBadge: null,
            delayByEmployee: null
        }
    },

    mixins: [ajaxCalls],

    mounted() {
        this.getBadgeData();
        this.getTicketsByEmployee();
        this.getCreationByHour();
        this.getJobSiteData();
        this.getClosedTasksByEmployee();
        this.getDelayByBadge();
        this.getDelayByEmployee();
    },
    methods: {
        getBadgeData() {
            this.asyncGetBadgeData().then((data) => {
                this.badgeData = data.data;
            }).catch(res => {console.log(res)});
        },
        getTicketsByEmployee() {
            this.asyncGetTicketsByEmployee().then((data) => {
                // console.log(data.data);
                this.ticketByEmployeeData = data.data
            })
        },
        getCreationByHour() {
            this.asyncGetCreationByHour().then((data) => {
                // console.log(data.data);
                this.creationByHour = data.data
            })
        },
        getJobSiteData() {
            this.asyncGetJobSiteData().then((data) => {
                // console.log(data.data);
                this.jobsiteData = data.data
            })
        },
        getClosedTasksByEmployee() {
            this.asyncGetClosedTasksByEmployee().then((data) => {
                // console.log(data.data);
                this.closedTasksByEmployee = data.data
            })
        },
        getDelayByBadge() {
            this.asyncGetDelayByBadge().then((data) => {
                // console.log(data.data);
                this.delayByBadge = data.data
            })
        },
        getDelayByEmployee() {
            this.asyncGetDelayByEmployee().then((data) => {
                console.log(data.data);
                this.delayByEmployee = data.data
            })
        },
    }
}
</script>
