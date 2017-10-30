import React from 'react';

import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import { linkTo } from '@storybook/addon-links';

import Settings from 'components/settings-form';
import ManualConfig from 'components/settings-form/manual-config';
import AutoConfig from 'components/settings-form/auto-config';

storiesOf('Настройки', module)
  .add('Собранная страница', () => <Settings />)
  .add('Ручная настройка', () => <ManualConfig onChange={() => action('onChange')} />)
  .add('Автоматическая настройка', () => <AutoConfig onChange={() => action('onChange')} />)
;
