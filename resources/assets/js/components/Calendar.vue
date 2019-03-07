<template>
    <div class="mini-calendar">
        <v-calendar v-on:dayclick="dayclick" :attributes="calendar"></v-calendar>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
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