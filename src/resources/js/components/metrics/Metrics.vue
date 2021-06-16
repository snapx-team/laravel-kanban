<template>
    <div>
        
        <pie-chart v-if="badgeData != null" :series="badgeData.hits" :labels="badgeData.names" :title="'Tickets by Category'"></pie-chart>
        <pie-chart v-if="ticketByEmployeeData != null" :series="ticketByEmployeeData.hits" :labels="ticketByEmployeeData.names" :title="'Tickets Created by Employee'"></pie-chart>
        <bar-chart  v-if="creationByHour != null" :data="creationByHour.hits" :categories="creationByHour.names" :xname="'Tickets'" :yname="'Hour'" :title="'Tickets by Hour Created'"></bar-chart>
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
        }
    },

    mixins: [ajaxCalls],

    mounted() {
        this.getBadgeData();
        this.getTicketsByEmployee();
        this.getCreationByHour();
    },
    methods: {
        getBadgeData() {
            this.asyncGetBadgeData().then((data) => {
                this.badgeData = data.data;
            }).catch(res => {console.log(res)});
        },
        getTicketsByEmployee() {
            this.asyncGetTicketsByEmployee().then((data) => {
                console.log(data.data);
                this.ticketByEmployeeData = data.data
            })
        },
        getCreationByHour() {
            this.asyncGetCreationByHour().then((data) => {
                console.log(data.data);
                this.creationByHour = data.data
            })
        }
    }
}
</script>
