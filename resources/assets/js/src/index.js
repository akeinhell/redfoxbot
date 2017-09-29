import 'bootstrap/dist/css/bootstrap.css'
import 'font-awesome/css/font-awesome.css'
import 'select2/dist/css/select2.css'
import 'react-forms-ui/lib/react-forms-ui.css'
import {initialize} from 'react-forms-ui'
import SettingsForm from './components/settings-form';
import React from 'react';
import ReactDOM from 'react-dom';
initialize();


const element = document.getElementById('root');
ReactDOM.render(<div><SettingsForm/></div>, element);