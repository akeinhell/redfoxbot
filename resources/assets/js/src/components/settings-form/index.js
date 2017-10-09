import escapeRegExp from 'lodash/escapeRegExp';
import filter from 'lodash/filter';
import React, {Component} from 'react';
import {Grid, Header, Search, Container, Form, Button, Checkbox, Dropdown} from 'semantic-ui-react';
import 'semantic-ui-css/semantic.css';
import styles from './style.less';

const projects = [
    {
        text: 'Redfox Авангард',
        value: 'RedfoxAvangard',
        image: {avatar: false, src: require('./icons/redfoxkrsk.ico')},
    },
    {
        text: 'Redfox Сафари/Штурм',
        value: 'RedfoxSafari',
        image: {avatar: false, src: require('./icons/redfoxkrsk.ico')},
    },
    {
        text: 'Dozor.Lite',
        value: 'DozorLite',
        image: {avatar: false, src: require('./icons/dozor.ico')},
    },
    {
        text: 'Экипаж',
        value: 'Ekipazh',
        image: {avatar: false, src: require('./icons/dozor.ico')},
    },
    {
        text: 'encounter',
        value: 'Encounter',
        image: {avatar: false, src: require('./icons/encounter.ico')},
    },
];

const source = [
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

export default class SettingsForm extends Component {
    componentWillMount() {
        this.resetComponent();
    }

    resetComponent = () => this.setState({isLoading: false, results: [], value: ''});

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

    render() {
        const {isLoading, value, results} = this.state;

        return (
          <Grid verticalAlign={'center'}>
              <Grid.Column width={12}>
                  <Container fluid>
                      <Header as='h2'>Настройки</Header>
                      <Button.Group fluid>
                          <Button>Ручная настройка</Button>
                          <Button.Or/>
                          <Button positive>Автоматическая настройка</Button>
                      </Button.Group>
                      <Form className={styles.form}>
                          <Form.Field>
                              <label>Выбири движочек</label>
                              <Dropdown placeholder='Выберите движок' fluid selection options={projects}/>
                          </Form.Field>

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
                              <Dropdown placeholder='Выберите движок' fluid selection options={projects}/>
                          </Form.Field>
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
                          </Form.Field>
                          <Form.Field>
                              <label>Логин</label>
                              <input placeholder='логин'/>
                          </Form.Field>
                          <Form.Field>
                              <label>Пароль</label>
                              <input placeholder='Пароль'/>
                          </Form.Field>
                          <Form.Field>
                              <label>Пин</label>
                              <input placeholder='Пин'/>
                          </Form.Field>
                          <Form.Field>
                              <Checkbox label='Автоматическая отправка кода'/>
                          </Form.Field>
                          <Button type='submit'>Получить ссылку</Button>
                      </Form>
                  </Container>
              </Grid.Column>
          </Grid>
        );
    }
}