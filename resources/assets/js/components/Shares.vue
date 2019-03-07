<template>

    <table class="table">
        <thead>
            <tr>
                <td style="width:70%">
                    <select class="form-control" style="width:100%" name="emails"></select>
                </td>
                <td style="width:30%">
                    <select class="form-control" name="permissions">
                        <option value="0">Can view</option>
                        <option value="1">Can edit</option>
                    </select>
                </td>
                <td>
                    <button v-on:click.prevent="addShare" class="btn btn-default">Add</button>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr v-for="share in shares">
                <td>{{share.email}}</td>
                <td>
                    <select v-model="share.pivot.permissions" class="form-control">
                        <option v-bind:value="0">Can view</option>
                        <option v-bind:value="1">Can edit</option>
                    </select>
                </td>
                <td>
                    <button v-bind:data-email="share.email" v-on:click.prevent="removeShare" class="btn btn-default">Remove</button>
                </td>
            </tr>
        </tbody>
    </table>

</template>

<script>
    export default {
        props: ['shares'],
        methods: {
            addShare: function (e) {
                var form = e.target.closest('form');
                this.shares.push({
                    email: form.elements.emails.value,
                    name: form.elements.emails.value,
                    pivot: {
                        permissions: form.elements.permissions.value
                    }
                });
            },
            removeShare: function (e) {
                var that = this;

                this.shares.forEach(function (item, index) {
                    if (item.email == e.target.dataset.email) {
                        that.shares.splice(index, 1);
                    }
                });
            }
        },
        mounted: function () {
            console.log('Shares mounted');
            $('[name=emails]', this.$el).selectize({
                delimiter: ',',
                persist: false,
                valueField: 'email',
                labelField: 'email',
                searchField: 'email',
                create: function(input) {
                    return {
                        email: input,
                        name: input
                    }
                },
                load: function(query, callback) {
                    if (!query.length) return callback();
                    $.ajax({
                        url: '/emails',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            q: query
                        },
                        error: function () {
                            callback();
                        },
                        success: function (res) {
                            callback(res.data);
                        }
                    });
                }
            });
        }
    }
</script>