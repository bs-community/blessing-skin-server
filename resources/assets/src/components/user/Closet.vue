<template>
    <section class="content">
        <email-verification />
        <div class="row">
            <!-- Left col -->
            <div class="col-md-8">
                <!-- Custom tabs -->
                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs">
                        <li :class="{ active: category === 'skin' }">
                            <a
                                href="#"
                                @click="switchCategory"
                                v-t="'general.skin'"
                                class="category-switch"
                                data-toggle="tab"
                            />
                        </li>
                        <li :class="{ active: category === 'cape' }">
                            <a
                                href="#"
                                @click="switchCategory"
                                v-t="'general.cape'"
                                class="category-switch"
                                data-toggle="tab"
                            />
                        </li>

                        <li class="pull-right" style="padding: 7px;">
                            <div class="has-feedback pull-right">
                                <div class="user-search-form">
                                    <input
                                        type="text"
                                        v-model="query"
                                        @input="search"
                                        class="form-control input-sm"
                                        :placeholder="$t('user.typeToSearch')"
                                    >
                                    <span class="glyphicon glyphicon-search form-control-feedback"></span>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="tab-content no-padding">
                        <div
                            v-if="category === 'skin'"
                            class="tab-pane box-body"
                            :class="{ active: category === 'skin' }"
                            id="skin-category"
                        >
                            <div v-if="skinItems.length === 0" class="empty-msg">
                                <div v-if="query !== ''" v-t="'general.noResult'"></div>
                                <div v-else v-html="$t('user.emptyClosetMsg', { url: linkToSkin })" />
                            </div>
                            <div v-else>
                                <closet-item
                                    v-for="(item, index) in skinItems"
                                    :key="item.tid"
                                    :tid="item.tid"
                                    :name="item.name"
                                    :type="item.type"
                                    :selected="selectedSkin === item.tid"
                                    @select="selectTexture(item.tid)"
                                    @item-removed="removeSkinItem(index)"
                                ></closet-item>
                            </div>
                        </div>
                        <div
                            v-else
                            class="tab-pane box-body"
                            :class="{ active: category === 'cape' }"
                            id="cape-category"
                        >
                            <div v-if="capeItems.length === 0" class="empty-msg">
                                <div v-if="query !== ''" v-t="'general.noResult'"></div>
                                <div v-else v-html="$t('user.emptyClosetMsg', { url: linkToCape })" />
                            </div>
                            <div v-else>
                                <closet-item
                                    v-for="(item, index) in capeItems"
                                    :key="item.tid"
                                    :tid="item.tid"
                                    :name="item.name"
                                    :type="item.type"
                                    :selected="selectedCape === item.tid"
                                    @select="selectTexture(item.tid)"
                                    @item-removed="removeCapeItem(index)"
                                ></closet-item>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <paginate
                            v-if="category === 'skin'"
                            v-model="skinCurrentPage"
                            :page-count="skinTotalPages"
                            class="pull-right"
                            container-class="pagination pagination-sm no-margin"
                            first-button-text="«"
                            prev-text="‹"
                            next-text="›"
                            last-button-text="»"
                            :click-handler="pageChanged"
                            :first-last-button="true"
                        ></paginate>
                        <paginate
                            v-else
                            v-model="capeCurrentPages"
                            :page-count="capeTotalPages"
                            class="pull-right"
                            container-class="pagination pagination-sm no-margin"
                            first-button-text="«"
                            prev-text="‹"
                            next-text="›"
                            last-button-text="»"
                            :click-handler="pageChanged"
                            :first-last-button="true"
                        ></paginate>
                    </div>
                </div><!-- /.nav-tabs-custom -->

            </div>

            <!-- Right col -->
            <div class="col-md-4">
                <previewer closet-mode :skin="skinUrl" :cape="capeUrl">
                    <template slot="footer">
                        <button
                            class="btn btn-primary"
                            data-toggle="modal"
                            data-target="#modal-use-as"
                            v-t="'user.useAs'"
                            @click="applyTexture"
                        ></button>
                        <button
                            class="btn btn-default pull-right"
                            v-t="'user.resetSelected'"
                            @click="resetSelected"
                        ></button>
                    </template>
                </previewer>
            </div>
        </div>

        <div
            id="modal-use-as"
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
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" v-t="'user.closet.use-as.title'"></h4>
                    </div>
                    <div class="modal-body">
                        <template v-if="players.length !== 0">
                            <div v-for="player in players" :key="player.pid" class="player-item">
                                <label class="model-label" :for="player.pid">
                                    <input
                                        type="radio"
                                        name="player"
                                        :value="player.pid"
                                        v-model="selectedPlayer"
                                    />
                                    <img :src="avatarUrl(player)" width="35" height="35" />
                                    <span>{{ player.player_name }}</span>
                                </label>
                            </div>
                        </template>
                        <p v-else v-t="'user.closet.use-as.empty'"></p>
                    </div>
                    <div class="modal-footer">
                        <a href="./player" class="btn btn-default pull-left" v-t="'user.closet.use-as.add'"></a>
                        <a class="btn btn-primary" v-t="'general.submit'" @click="submitApplyTexture"></a>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </section><!-- /.content -->
</template>

<script>
import toastr from 'toastr';
import Paginate from 'vuejs-paginate';
import ClosetItem from './ClosetItem';
import EmailVerification from './EmailVerification';
import { debounce, queryString } from '../../js/utils';
import { swal } from '../../js/notify';

export default {
    name: 'Closet',
    components: {
        Paginate,
        ClosetItem,
        Previewer: () => import('../common/Previewer'),
        EmailVerification,
    },
    data: () => ({
        category: 'skin',
        query: '',
        skinItems: [],
        skinCurrentPage: 1,
        skinTotalPages: 1,
        capeItems: [],
        capeCurrentPages: 1,
        capeTotalPages: 1,
        selectedSkin: 0,
        skinUrl: '',
        selectedCape: 0,
        capeUrl: '',
        players: [],
        selectedPlayer: 0,
        linkToSkin: `${blessing.base_url}/skinlib?filter=skin`,
        linkToCape: `${blessing.base_url}/skinlib?filter=cape`,
    }),
    created() {
        this.search = debounce(this.loadCloset, 350);
    },
    beforeMount() {
        this.loadCloset();
    },
    mounted() {
        const tid = +queryString('tid', 0);
        if (tid) {
            this.selectTexture(tid);
            this.applyTexture();
            $('#modal-use-as').modal();
        }
    },
    methods: {
        /* istanbul ignore next */
        search() {},
        async loadCloset(page = 1) {
            const { items, category, total_pages } = await this.$http.get(
                '/user/closet-data',
                {
                    category: this.category,
                    q: this.query,
                    page,
                }
            );
            this[`${category}TotalPages`] = total_pages;
            this[`${category}Items`] = items;
        },
        removeSkinItem(index) {
            this.$delete(this.skinItems, index);
        },
        removeCapeItem(index) {
            this.$delete(this.capeItems, index);
        },
        switchCategory() {
            this.category = this.category === 'skin' ? 'cape' : 'skin';
            this.loadCloset();
        },
        pageChanged(page) {
            this.loadCloset(page);
        },
        avatarUrl(player) {
            const tid = player.preference === 'default' ? player.tid_steve : player.tid_alex;
            return `${blessing.base_url}/avatar/35/${tid}`;
        },
        async selectTexture(tid) {
            const { type, hash } = await this.$http.get(`/skinlib/info/${tid}`);
            if (type === 'cape') {
                this.capeUrl = `/textures/${hash}`;
                this.selectedCape = tid;
            } else {
                this.skinUrl = `/textures/${hash}`;
                this.selectedSkin = tid;
            }
        },
        async applyTexture() {
            this.players = await this.$http.get('/user/player/list');
            setTimeout(() => {
                $(this.$el).iCheck({
                    radioClass: 'iradio_square-blue',
                    checkboxClass: 'icheckbox_square-blue'
                }).on('ifChanged', function () {
                    $(this)[0].dispatchEvent(new Event('change'));
                });
            }, 0);
        },
        async submitApplyTexture() {
            if (!this.selectedPlayer) {
                return toastr.info(this.$t('user.emptySelectedPlayer'));
            }

            if (!this.selectedSkin && !this.selectedCape) {
                return toastr.info(this.$t('user.emptySelectedTexture'));
            }

            const { errno, msg } = await this.$http.post(
                '/user/player/set',
                {
                    pid: this.selectedPlayer,
                    tid: {
                        skin: this.selectedSkin || undefined,
                        cape: this.selectedCape || undefined
                    }
                }
            );
            if (errno === 0) {
                swal({ type: 'success', text: msg });
                $('#modal-use-as').modal('hide');
            } else {
                toastr.warning(msg);
            }
        },
        resetSelected() {
            this.selectedSkin = this.selectedCape = 0;
            this.skinUrl = this.capeUrl = '';
        }
    },
};
</script>

<style lang="stylus">
.empty-msg {
    text-align: center;
    font-size: 16px;
    padding: 10px 0;
}

.texture-name {
    width: 65%;
    display: inline-block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;

    small {
        font-size: 75%;
    }
}

.item-footer > .dropdown-menu {
    margin-left: 180px;
}

.box-title {
    a {
        color: #6d6d6d;
    }

    a.selected {
        color: #3c8dbc;
    }
}

.player-item:not(:nth-child(1)) {
    margin-top: 10px;
}

.breadcrumb {
    a {
        margin-right: 10px;
        color: #444;
    }

    a:hover {
        color: #3c8dbc;
    }
}
</style>
