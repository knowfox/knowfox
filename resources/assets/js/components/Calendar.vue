<template>
    <div class="mini-calendar">
        <v-calendar v-on:dayclick="dayclick" :attributes="calendar" :first-day-of-week="2"></v-calendar>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Calendar mounted.')
        },
        methods: {
            dayclick: function (event) {
                let month = event.month.toString(),
                    day = event.day.toString();

                if (month.length < 2) {
                    month = '0' + month;
                }
                if (day.length < 2) {
                    day = '0' + day;
                }

                location.href = `/${event.year}-${month}-${day}`;
            }
        },
        data() {
            return {
                calendar: [
                    {
                        key: 'today',
                        highlight: {
                            backgroundColor: '#ff8080',
                            height: '2em'
                        },
                        contentStyle: {
                            color: '#fafafa'
                        },
                        dates: new Date()
                    }
                ]
            };
        }
    };
</script>