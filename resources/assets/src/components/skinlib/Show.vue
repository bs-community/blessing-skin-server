<template>
    <div class="row">
        <div class="col-md-8">
            <previewer
                :skin="type !== 'cape' && textureUrl"
                :cape="type === 'cape' ? textureUrl : ''"
                :init-position-z="60"
            >
                <template slot="footer">
                    <button
                        v-if="!auth"
                        disabled
                        :title="$t('skinlib.show.anonymous')"
                        class="btn btn-primary pull-right"
                        v-t="'skinlib.addToCloset'"
                    ></button>
                    <template v-else>
                        <a
                            v-if="liked"
                            @click="removeFromCloset"
                            class="btn btn-primary pull-right"
                            v-t="'skinlib.removeFromCloset'"
                        ></a>
                        <a
                            v-else
                            @click="addToCloset"
                            class="btn btn-primary pull-right"
                            v-t="'skinlib.addToCloset'"
                        ></a>
                    </template>
                    <div
                        class="btn likes"
                        style="cursor: auto;"
                        :style="{ color: liked ? '#e0353b' : '#333' }"
                        :title="$t('skinlib.show.likes')"
                        data-toggle="tooltip"
                        data-placement="top"
                    >
                        <i class="fas fa-heart"></i>
                        <span>{{ likes }}</span>
                    </div>
                </template>
            </previewer>
        </div>

        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title" v-t="'skinlib.show.detail'"></h3>
                </div>
                <div class="box-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td v-t="'skinlib.show.name'"></td>
                                <td>{{ name }}
                                    <small v-if="uploader === currentUid || admin">
                                        <a href="#" @click="changeTextureName" v-t="'skinlib.show.edit'"></a>
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <td v-t="'skinlib.show.model'"></td>
                                <td>
                                    <template v-if="type === 'cape'">{{ $t('general.cape') }}</template>
                                    <template v-else>{{ type }}</template>
                                    <small v-if="uploader === currentUid || admin">
                                        <a href="#" @click="changeModel" v-t="'skinlib.show.edit'"></a>
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <td>TID</td>
                                <td>{{ tid }}</td>
                            </tr>
                            <tr>
                                <td>Hash
                                    <i
                                        v-if="canBeDownloaded"
                                        class="fas fa-question-circle"
                                        :title="$t('skinlib.show.download-raw')"
                                        data-toggle="tooltip"
                                        data-placement="top"
                                    ></i>
                                </td>
                                <td>
                                    <a v-if="canBeDownloaded" :href="`${baseUrl}/raw/${tid}.png`" :title="hash">{{ hash.slice(0, 15) }}...</a>
                                    <span v-else :title="hash">{{ hash.slice(0, 15) }}...</span>
                                </td>
                            </tr>
                            <tr>
                                <td v-t="'skinlib.show.size'"></td>
                                <td>{{ size }} KB</td>
                            </tr>
                            <tr>
                                <td v-t="'skinlib.show.uploader'"></td>
                                <template v-if="uploaderNickName != null">
                                    <td><a :href="`${baseUrl}/skinlib?filter=${type === 'cape' ? 'cape' : 'skin'}&uploader=${uploader}`">{{ uploaderNickName }}</a></td>
                                </template>
                                <template v-else>
                                    <td><span v-t="'general.unexistent-user'"></span></td>
                                </template>
                            </tr>
                            <tr>
                                <td v-t="'skinlib.show.upload-at'"></td>
                                <td>{{ uploadAt }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->

            <div v-if="auth" class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title" v-t="'admin.operationsTitle'" />
                </div><!-- /.box-header -->
                <div class="box-body">
                    <p v-t="'skinlib.show.manage-notice'"></p>
                </div><!-- /.box-body -->

                <div class="box-footer">
                    <a @click="togglePrivacy" class="btn btn-warning" v-t="togglePrivacyText"></a>
                    <a @click="deleteTexture" class="btn btn-danger pull-right" v-t="'skinlib.show.delete-texture'"></a>
                </div><!-- /.box-footer -->
            </div>
        </div>
    </div>
</template>

<script>
import { swal } from '../../js/notify';
import toastr from 'toastr';

export default {
    name: 'Show',
    components: {
        Previewer: () => import('../common/Previewer')
    },
    props: {
        baseUrl: {
            default: blessing.base_url
        }
    },
    data() {
        return {
            tid: +this.$route[1],
            name: '',
            type: 'steve',
            likes: 0,
            hash: '',
            uploader: 0,
            size: 0,
            uploadAt: '',
            public: true,
            liked: __bs_data__.inCloset,
            canBeDownloaded: __bs_data__.download,
            currentUid: __bs_data__.currentUid,
            admin: __bs_data__.admin,
            uploaderNickName: __bs_data__.nickname,
        };
    },
    computed: {
        auth() {
            return !!this.currentUid;
        },
        togglePrivacyText() {
            return this.public ? 'skinlib.setAsPrivate' : 'skinlib.setAsPublic';
        },
        textureUrl() {
            return `${this.baseUrl}/textures/${this.hash}`;
        }
    },
    beforeMount() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            const data = await this.$http.get(`/skinlib/info/${this.tid}`);
            this.name = data.name;
            this.type = data.type;
            this.likes = data.likes;
            this.hash = data.hash;
            this.uploader = data.uploader;
            this.size = data.size;
            this.uploadAt = data.upload_at;
            this.public = !!data.public;
        },
        async addToCloset() {
            const { dismiss, value } = await swal({
                title: this.$t('skinlib.setItemName'),
                text: this.$t('skinlib.applyNotice'),
                inputValue: this.name,
                input: 'text',
                showCancelButton: true,
                inputValidator: value => !value && this.$t('skinlib.emptyItemName')
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/user/closet/add',
                { tid: this.tid, name: value }
            );
            if (errno === 0) {
                this.liked = true;
                this.likes++;
                swal({ type: 'success', text: msg });
            } else {
                toastr.warning(msg);
            }
        },
        async removeFromCloset() {
            const { dismiss } = await swal({
                text: this.$t('user.removeFromClosetNotice'),
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#3085d6',
                confirmButtonColor: '#d33'
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/user/closet/remove',
                { tid: this.tid }
            );
            if (errno === 0) {
                this.liked = false;
                this.likes--;
                swal({ type: 'success', text: msg });
            } else {
                toastr.warning(msg);
            }
        },
        async changeTextureName() {
            const { dismiss, value } = await swal({
                text: this.$t('skinlib.setNewTextureName'),
                input: 'text',
                inputValue: this.name,
                showCancelButton: true,
                inputValidator: name => !name && this.$t('skinlib.emptyNewTextureName')
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/skinlib/rename',
                { tid: this.tid, new_name: value }
            );
            if (errno === 0) {
                this.name = value;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async changeModel() {
            const { dismiss, value } = await swal({
                text: this.$t('skinlib.setNewTextureModel'),
                input: 'select',
                inputValue: this.type,
                inputOptions: {
                    steve: 'Steve',
                    alex: 'Alex',
                    cape: this.$t('general.cape')
                },
                showCancelButton: true,
                inputClass: 'form-control'
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/skinlib/model',
                { tid: this.tid, model: value }
            );
            if (errno === 0) {
                this.type = value;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async togglePrivacy() {
            const { dismiss } = await swal({
                text: this.public
                    ? this.$t('skinlib.setPrivateNotice')
                    : this.$t('skinlib.setPublicNotice'),
                type: 'warning',
                showCancelButton: true
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/skinlib/privacy',
                { tid: this.tid }
            );
            if (errno === 0) {
                toastr.success(msg);
                this.public = !this.public;
            } else {
                toastr.warning(msg);
            }
        },
        async deleteTexture() {
            const { dismiss } = await swal({
                text: this.$t('skinlib.deleteNotice'),
                type: 'warning',
                showCancelButton: true
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/skinlib/delete',
                { tid: this.tid }
            );
            if (errno === 0) {
                await swal({ type: 'success', text: msg });
                window.location = `${this.baseUrl}/skinlib`;
            } else {
                swal({ type: 'warning', text: msg });
            }
        }
    }
};
</script>

<style lang="stylus">
.table > tbody > tr > td {
    border-top: 0;
}
</style>
