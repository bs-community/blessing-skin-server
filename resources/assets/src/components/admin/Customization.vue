<template>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title" v-t="'admin.change-color.title'"></h3>
        </div>
        <div class="box-body no-padding">
            <table class="table table-striped bring-up nth-2-center">
                <tbody>
                    <template v-for="color in colors">
                        <tr v-for="innerColor in [color, `${color}-light`]" :key="innerColor">
                            <td v-t="`admin.colors.${innerColor}`"></td>
                            <td>
                                <a
                                    href="#"
                                    @click="preview(innerColor)"
                                    :class="`btn bg-${color} btn-xs`"
                                >
                                    <i class="far fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            <button @click="submit" class="btn btn-primary" v-t="'general.submit'"></button>
        </div>
    </div>
</template>

<script>
import toastr from 'toastr';

export default {
    name: 'Customization',
    data() {
        return {
            colors: [
                'blue',
                'yellow',
                'green',
                'purple',
                'red',
                'black',
            ],
            currentSkin: blessing.extra.currentSkin
        };
    },
    methods: {
        preview(color) {
            document.body.classList.replace(this.currentSkin, `skin-${color}`);
            this.currentSkin = `skin-${color}`;
        },
        async submit() {
            const { msg } = await this.$http.post(
                '/admin/customize?action=color',
                { color_scheme: this.currentSkin }
            );
            toastr.success(msg);
        }
    }
};
</script>
