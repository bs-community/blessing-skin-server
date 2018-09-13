<template>
    <span>
        <button
            v-if="!updating"
            class="btn btn-primary"
            :disabled="!canUpdate"
            @click="update"
        >{{ $t('admin.updateButton') }}</button>
        <button v-else disabled class="btn btn-primary">
            <i class="fa fa-spinner fa-spin"></i> {{ $t('admin.preparing') }}
        </button>

        <div
            id="modal-start-download"
            class="modal fade"
            tabindex="-1"
            role="dialog"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        ><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" v-t="'admin.downloading'"></h4>
                    </div>
                    <div class="modal-body">
                        <p>{{ $t('admin.updateSize') }}<span>{{ total }}</span> KB</p>
                        <div class="progress">
                            <div
                                class="progress-bar progress-bar-striped active"
                                role="progressbar"
                                aria-valuenow="0"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                :style="{ width: `${percentage}%` }"
                            >
                                <span>{{ ~~percentage }}</span>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </span>
</template>

<script>
import { swal } from '../../js/notify';

const POLLING_INTERVAL = 500;

export default {
    name: 'UpdateButton',
    data: () => ({
        canUpdate: blessing.extra.canUpdate,
        updating: false,
        total: 0,
        downloaded: 0,
    }),
    computed: {
        percentage() {
            return this.downloaded / this.total * 100;
        }
    },
    methods: {
        async update() {
            this.updating = true;

            await this.takeAction('prepare-download');

            this.updating && $('#modal-start-download').modal({
                backdrop: 'static',
                keyboard: false
            });

            setTimeout(this.polling, POLLING_INTERVAL);

            this.updating && await this.takeAction('start-download');

            this.updating && await this.takeAction('extract');

            this.updating = false;
            if (this.downloaded) {
                await swal({ type: 'success', text: this.$t('admin.updateCompleted') });
                window.location = blessing.base_url;
            }
        },
        async takeAction(action) {
            const { errno, msg } = await this.$http.post('/admin/update/download', {
                action
            });
            if (errno && errno !== 0) {
                swal({ type: 'error', text: msg });
                this.updating = false;
            }
        },
        async polling() {
            const { downloaded, total } = await this.$http.get(
                '/admin/update/download',
                { action: 'get-progress' }
            );
            this.downloaded = ~~(+downloaded / 1024);
            this.total = ~~(+total / 1024);

            this.updating && setTimeout(this.polling, POLLING_INTERVAL);
        }
    }
};
</script>
