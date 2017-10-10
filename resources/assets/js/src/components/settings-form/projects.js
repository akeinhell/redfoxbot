const AUTH_TYPE = {
    pin: 'pin',
    password: 'password'
};

export const RedfoxAvangard = {
    key: 'RedfoxAvangard',
    value: 'RedfoxAvangard',
    text: 'Redfox Авангард',
    "data-auth-type": AUTH_TYPE.password,
    image: {avatar: false, src: require('./icons/redfoxkrsk.ico')},
};

export const RedfoxSafari = {
    key: 'RedfoxSafari',
    value: 'RedfoxSafari',
    text: 'Redfox Сафари/Штурм',
    "data-auth-type": AUTH_TYPE.password,
    image: {avatar: false, src: require('./icons/redfoxkrsk.ico')},
};

export const DozorLite = {
    key: 'DozorLite',
    value: 'DozorLite',
    text: 'Dozor.Lite',
    "data-auth-type": AUTH_TYPE.pin,
    image: {avatar: false, src: require('./icons/dozor.ico')},
};

export const Ekipazh = {
    key: 'Ekipazh',
    value: 'Ekipazh',
    text: 'Экипаж',
    "data-auth-type": AUTH_TYPE.pin,
    image: {avatar: false, src: require('./icons/dozor.ico')},
};

export const Encounter = {
    key: 'Encounter',
    text: 'encounter',
    value: 'Encounter',
    "data-auth-type": AUTH_TYPE.password,
    image: {avatar: false, src: require('./icons/encounter.ico')},
};

export const QuestUa = {
    key: 'QuestUa',
    text: 'QuestUa',
    value: 'QuestUa',
    "data-auth-type": AUTH_TYPE.pin,
    image: {avatar: false, src: require('./icons/encounter.ico')},
};