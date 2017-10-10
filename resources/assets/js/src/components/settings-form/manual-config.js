import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {Dropdown, Form, Search} from 'semantic-ui-react';


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

const searchSource = [
    {
        'title': 'Jacobson, Connelly and Pagac',
        'description': 'Distributed leading edge firmware',
        'image': 'https://s3.amazonaws.com/uifaces/faces/twitter/elisabethkjaer/128.jpg',
        'price': '$$44.45'
    },
    {
        'title': 'Labadie - Moore',
        'description': 'Distributed 4th generation moratorium',
        'image': 'https://s3.amazonaws.com/uifaces/faces/twitter/lonesomelemon/128.jpg',
        'price': '$5.8 1'
    },
    {
        'title': 'Hirthe LLC',
        'description': 'Synergized 6th generation product',
        'image': 'https://s3.amazonaws.com/uifaces/faces/twitter/danro/128.jpg',
        'price': '$14.47'
    },
    {
        'title': 'Wolff - Ritchie',
        'description': 'Horizontal discrete pricing structure',
        'image': 'https://s3.amazonaws.com/uifaces/faces/twitter/mirfanqureshi/128.jpg',
        'price': '$$80.97'
    },
    {
        'title': 'Terry Inc',
        'description': 'Switchable zero tolerance knowledge user',
        'image': 'https://s3.amazonaws.com/uifaces/faces/twitter/hiemil/128.jpg',
        'price': '$$4.47'
    }
];



export default class ManualConfig extends Component {
    static propTypes = {
        onChange: PropTypes.func.isRequired,
    };

    constructor(props) {
        super(props);
        this.state = {};
    }

    handleProjectSelect(event, target) {
        const {value} = target;
        const authType = projectList.find(e => value === e.value)['data-auth-type'];
        this.setState({'auth': authType});
        this.props.onChange('project')(event, target)
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
                results: filter(searchSource, isMatch),
            });
        }, 50);
    };

    componentWillMount() {
        this.resetComponent();
    }

    resetComponent = () => this.setState({isLoading: false, results: [], value: ''});

    render() {
        const {isLoading, value, results} = this.state;
        return <div>
            <Form.Field>
                <label>Выберите движок</label>
                <Dropdown
                  placeholder='Выберите движок' fluid selection options={projectList}
                  onChange={this.handleProjectSelect.bind(this)}
                />
            </Form.Field>

            { this.state.auth && <Form.Field>
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
            </Form.Field>
            }

            {
                this.state.auth === projects.AUTH_TYPE.password && (
                  <div>
                      <Form.Field>
                          <label> Логин </label>
                          <input placeholder='логин' onChange={this.props.onChange('login')}/>
                      </Form.Field>
                      <Form.Field>
                          <label>Пароль</label>
                          <input placeholder='Пароль' onChange={this.props.onChange('password')}/>
                      </Form.Field>
                  </div>
                )
            }
            {
                this.state.auth === projects.AUTH_TYPE.pin && (
                  <Form.Field>
                      <label> Пин </label>
                      <input placeholder='Пин' onChange={this.props.onChange('pin')}/>
                  </Form.Field>
                )
            }


        </div>;
    }
}