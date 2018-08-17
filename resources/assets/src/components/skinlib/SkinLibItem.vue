<template>
    <a :href="urlToDetail">
        <div class="item">
            <div class="item-body">
                <img :src="urlToPreview">
            </div>

            <div class="item-footer">
                <p class="texture-name">
                    <span :title="name">{{ name }}
                        <small>{{ $t('skinlib.filter.' + type) }}</small>
                    </span>
                </p>

                <a
                    :title="likeActionText"
                    class="more like"
                    :class="{ liked, anonymous }"
                    href="#"
                    @click.stop="toggleLiked"
                ><i class="fas fa-heart"></i></a>

                <small v-if="!isPublic" class="more private-label">
                    {{ $t('skinlib.private') }}
                </small>
            </div>
        </div>
    </a>
</template>

<script>
import { swal } from '../../js/notify';
import toastr from 'toastr';

export default {
    name: 'SkinLibItem',
    props: {
        tid: Number,
        name: String,
        type: {
            validator: value => ['steve', 'alex', 'cape'].includes(value)
        },
        liked: Boolean,
        anonymous: Boolean,
        isPublic: Boolean  // `public` is a reserved keyword
    },
    computed: {
        urlToDetail() {
            return `${blessing.base_url}/skinlib/show/${this.tid}`;
        },
        urlToPreview() {
            return `${blessing.base_url}/preview/${this.tid}.png`;
        },
        likeActionText() {
            if (this.anonymous) return this.$t('skinlib.anonymous');

            return this.liked
                ? this.$t('skinlib.removeFromCloset')
                : this.$t('skinlib.addToCloset');
        }
    },
    methods: {
        toggleLiked() {
            if (this.anonymous) {
                return;
            }

            if (this.liked) {
                this.removeFromCloset();
            } else {
                this.addToCloset();
            }
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
                swal({ type: 'success', text: msg });
                this.$emit('like-toggled', true);
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
                this.$emit('like-toggled', false);
                swal({ type: 'success', text: msg });
            } else {
                toastr.warning(msg);
            }
        }
    }
};
</script>

<style lang="stylus">
.texture-name {
    width: 65%;
    display: inline-block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (min-width: 1200px) {
    .item {
        width: 250px;
    }

    .item-body > img {
        margin-left: 50px;
    }

    .texture-name {
        width: 65%;
    }
}

.item-footer {
    a {
        color: #fff;
    }

    .like:hover,
    .liked {
        color: #e0353b;
    }
}

.private-label {
    margin-top: 2px;
}
</style>
