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
        this.setState({'project': target.value});
        this.props.onChange('project')(event, target);
    };

    handleResultSelect = (e, {result}) => this.setState({value: result.title});

    handleSearchChange = (e, {value}) => {
        this.setState({isLoading: true, value});

        fetch(`/api/games/?q=${value}`)
          .then(r => r.json())
          .then(results => {
              return  this.setState({
                  results: results.map(game => ({
                          key: game.gid,
                          title: game.title,
                          description: game.city.title
                  })),
                  isLoading: false,
              });
          })
          .catch(e => {
              console.error(e);
              this.setState({
                  results: [],
                  isLoading: false,
              });
          });
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

            {(this.state.project === 'Encounter') &&
                <Form.Field>
                    <label>Выбери игру</label>
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