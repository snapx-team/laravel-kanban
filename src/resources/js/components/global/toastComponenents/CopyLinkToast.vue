<template>
    <div class="container">
        <button
            class="action"
            @click.stop="copyToClipboard"
        >{{ message }}
        </button>
    </div>
</template>

<script>

export default {

    props: {
        message: {
            type: String,
        },
        url: {
            type: String,
        },
    },

    methods: {
        clicked() {
            this.$emit("myClick");
        },

        copyToClipboard() {
            let temp = document.createElement('input'),
                text = this.url;

            document.body.appendChild(temp);
            temp.value = text;
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);

            this.$toast.success("link copied to clipboard", {
                position: 'bottom-right',
                timeout: 5000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: false,
                closeButton: 'button',
                icon: true,
                rtl: false
            });
        }
    }
};
</script>
