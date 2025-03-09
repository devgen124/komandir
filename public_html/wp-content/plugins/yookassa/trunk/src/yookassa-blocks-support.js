import { decodeEntities } from '@wordpress/html-entities';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry
const ownerGateways = window.yookassaOwnPaymentMethods;
const paymentGateways = window.wc.wcSettings.allSettings.paymentMethodData;

Object.keys(paymentGateways).forEach(function(gatewayKey) {

    if (ownerGateways.indexOf(gatewayKey) >= 0) {

        const settings = paymentGateways[gatewayKey]
        const label = decodeEntities( settings.title )

        const Content = () => {
            return decodeEntities( settings.description || '' )
        }

        const Icon = () => {
            return settings.icon
                ? <img src={settings.icon} style={{ float: 'right', marginRight: '20px' }}  alt={settings.title}/>
                : ''
        }

        const Label = () => {
            return (
                <span style={{ width: '100%' }}>
                    {label}
                    <Icon />
                </span>
            )
        }
        registerPaymentMethod( {
            name: gatewayKey,
            label: <Label />,
            content: <Content />,
            edit: <Content />,
            canMakePayment: () => true,
            ariaLabel: label,
            supports: {
                features: settings.supports,
            }
        } )
    }
});
