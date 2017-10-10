import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {Search, Form, Dropdown} from 'semantic-ui-react';

import * as projects from './projects';
import escapeRegExp from 'lodash/escapeRegExp';
import filter from 'lodash/filter';
const projectList = [
    projects.RedfoxAvangard,
    projects.RedfoxSafari,
    projects.Encounter,
    projects.QuestUa,
    projects.DozorLite,
    projects.Ekipazh,
];


export default class ManualConfig extends Component {
    static propTypes = {
        onChange: PropTypes.func.isRequired,
    };

    handleResultSelect = (e, {result}) => this.setState({value: result.title});

    handleSearchChange = (e, {value}) => {
        this.setState({isLoading: true, value});

        setTimeout(() => {
            if (this.state.value.length < 1) return this.resetComponent();

            const re = new RegExp(escapeRegExp(this.state.value), 'i');
            const isMatch = result => re.test(result.title);

            this.setState({
                isLoading: false,
                results: filter(source, isMatch),
            });
        }, 5000);
    };

    componentWillMount() {
        this.resetComponent();
    }

    resetComponent = () => this.setState({isLoading: false, results: [], value: ''});

    render() {
        const {isLoading, value, results} = this.state;
        return <div>
            <Form.Field>
                <label>Выбери город</label>
                <Search
                  input={{fluid: true}}
                  loading={isLoading}
                  onResultSelect={this.handleResultSelect}
                  onSearchChange={this.handleSearchChange}
                  results={results}
                  value={value}
                  {...this.props}
                />
            <Form.Field>
                <label>Выберите движок</label>
                <Dropdown
                  placeholder='Выберите движок' fluid selection options={projectList}
                  onChange={this.props.onChange('project')}
                />
            </Form.Field>
            </Form.Field>
            <Form.Field>
                <label> Логин </label>
                <input placeholder='логин' onChange={this.props.onChange('login')}/>
            </Form.Field>;
            <Form.Field>
                <label>Пароль</label>
                <input placeholder='Пароль' onChange={this.props.onChange('password')}/>
            </Form.Field>
            < Form.Field>
                <label> Пин </label>
                <input placeholder='Пин' onChange={this.props.onChange('pin')}/>
            </Form.Field>
        </div>;
    }
}