<template>
    <div  class="form-components__block custom-auth-block">
        <div :class="{'opacity-80': sending}" class="form-components__block-item block-button btn-full-width">
            <a @click.prevent="connectWallet" href="#" class="gsi-material-button">
                <div class="gsi-material-button-state"></div>
                <div class="gsi-material-button-content-wrapper">
                    <div class="gsi-material-button-icon">
                        <svg version="1.1" baseProfile="basic" id="Layer_1"
                                                     xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 387.6 237.6"
                                                     xml:space="preserve">
                        <path id="WalletConnect_00000073703063438220642730000002917717552236472496_" fill="#3B99FC" d="M79.4,46.4
                            c63.2-61.9,165.7-61.9,228.9,0l7.6,7.4c3.2,3.1,3.2,8.1,0,11.2l-26,25.5c-1.6,1.5-4.1,1.5-5.7,0l-10.5-10.3
                            c-44.1-43.2-115.6-43.2-159.7,0l-11.2,11c-1.6,1.5-4.1,1.5-5.7,0L71,65.8c-3.2-3.1-3.2-8.1,0-11.2L79.4,46.4z M362.1,99.1l23.2,22.7
                            c3.2,3.1,3.2,8.1,0,11.2L280.8,235.3c-3.2,3.1-8.3,3.1-11.4,0c0,0,0,0,0,0l-74.1-72.6c-0.8-0.8-2.1-0.8-2.9,0c0,0,0,0,0,0
                            l-74.1,72.6c-3.2,3.1-8.3,3.1-11.4,0c0,0,0,0,0,0L2.4,133c-3.2-3.1-3.2-8.1,0-11.2l23.2-22.7c3.2-3.1,8.3-3.1,11.4,0l74.1,72.6
                            c0.8,0.8,2.1,0.8,2.9,0c0,0,0,0,0,0l74.1-72.6c3.2-3.1,8.3-3.1,11.4,0c0,0,0,0,0,0l74.1,72.6c0.8,0.8,2.1,0.8,2.9,0l74.1-72.6
                            C353.8,96,358.9,96,362.1,99.1z"/>
                        </svg>

                    </div>
                    <span class="gsi-material-button-contents">{{ $t('Connect Wallet')}}</span>
                    <span style="display: none;">{{ $t('Connect Wallet')}}</span>
                    <span class="loader-svg-icon">
                        <svg v-show="sending" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    </div>
</template>
<script>


import { arbitrum, mainnet, optimism, polygon, sepolia } from '@reown/appkit/networks'
import { createAppKit } from '@reown/appkit'
import { WagmiAdapter } from '@reown/appkit-adapter-wagmi'
const networks = [arbitrum, mainnet, optimism, polygon, sepolia]
const projectId = process.env.MIX_WALLET_CONNECT_PROJECT_ID;

const wagmiAdapter = new WagmiAdapter({
    projectId,
    networks,
})

export const modal = createAppKit({
    adapters: [wagmiAdapter],
    networks,
    projectId,
    themeMode: 'light',
    themeVariables: {
        '--w3m-accent': '#000000',
    },
    features: {
        analytics: true,
        socials: false,
        email: false,
    }
})

export default {
    data() {
        return {
            sending: false,
            address: '',
            connected: false,
            firstInit: true,
        }
    },
    mounted() {

        modal.disconnect();

        modal.subscribeState(state => {
            this.connected = modal.getIsConnectedState();

            if(this.firstInit) {
                modal.disconnect();
                this.firstInit = false;
            }
        })

        modal.subscribeAccount(state => {
            this.address = state.address;
        })

        modal.subscribeProviders(state => {
            if(this.connected) {
                this.signMessage(state['eip155']);
            }
        })
    },
    methods: {
        async connectWallet() {

            if(this.sending) return;

            this.sending = true;

            try {
                modal.open();
            } catch (err) {
                this.sending = false;
            } finally {

            }
        },
        async signMessage(provider) {

            if(!provider) return;

            let message = "Allowing to login to the platform";

            const signature = await provider.request({
                method: 'personal_sign',
                params: [message, this.address]
            });

            axios.post(this.route('auth.walletconnect'), {
                address: this.address,
                signature: signature,
                message: message,
            }).then((response) => {
                modal.disconnect()
                this.$inertia.visit(this.route('home'));
                this.sending = false;
            }).catch(error => {
                modal.disconnect()
                this.$toast.error(this.$t("An error occurred while connecting your wallet."));
                this.sending = false;
            });
        }
    }
}
</script>
