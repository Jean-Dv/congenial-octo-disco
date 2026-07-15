import './theme.css';
import manifest from './theme.json';

import ThemeAlert from '../../js/Components/AerisAlert.vue';
import ThemeBadge from '../../js/Components/AerisBadge.vue';
import ThemeButton from '../../js/Components/AerisButton.vue';
import ThemeCard from '../../js/Components/AerisCard.vue';
import ThemeCheckbox from '../../js/Components/AerisCheckbox.vue';
import ThemeField from '../../js/Components/AerisField.vue';
import ThemeIconButton from '../../js/Components/AerisIconButton.vue';
import ThemeInput from '../../js/Components/AerisInput.vue';
import ThemeLogo from '../../js/Components/AerisLogo.vue';
import ThemeNavLink from '../../js/Components/AerisNavLink.vue';
import ThemeSelect from '../../js/Components/AerisSelect.vue';
import ThemeSwitch from '../../js/Components/AerisSwitch.vue';
import ThemeTable from '../../js/Components/AerisTable.vue';
import ThemeAppLayout from '../../js/Layouts/AppLayout.vue';
import ThemeGuestLayout from '../../js/Layouts/GuestLayout.vue';
import ThemePublicLayout from '../../../Modules/Public/resources/js/Layouts/PublicLayout.vue';

export default {
    id: manifest.id,
    progressColor: manifest.progress_color,
    components: {
        ThemeAlert,
        ThemeBadge,
        ThemeButton,
        ThemeCard,
        ThemeCheckbox,
        ThemeField,
        ThemeIconButton,
        ThemeInput,
        ThemeLogo,
        ThemeNavLink,
        ThemeSelect,
        ThemeSwitch,
        ThemeTable,
    },
    layouts: {
        ThemeAppLayout,
        ThemeGuestLayout,
        ThemePublicLayout,
    },
};
