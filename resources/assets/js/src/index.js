import {initialize} from 'react-forms-ui'
import SettingsForm from './components/settings-form';
import React from 'react';
import ReactDOM from 'react-dom';
initialize();


const element = document.getElementById('root');
ReactDOM.render(<div><SettingsForm/></div>, element);