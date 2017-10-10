import React, {Component} from 'react';
import {Button, Checkbox, Container, Form, Grid, Header} from 'semantic-ui-react';
import 'semantic-ui-css/semantic.css';
import styles from './style.less';
import AutoConfig from './auto-config';
import ManualConfig from './manual-config';


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

const defaultState = {
    isAuto: false,
};

export default class SettingsForm extends Component {

    constructor(props) {
        super(props);
        this.state = Object.assign({}, defaultState, props);
    }

    handleAutoClick(event, t) {
        const isAuto = t.checked;
        this.setState({isAuto});
    }

    handleChangeState(name) {
        return (event, target) => {
            const value = (target || event.target).value;
            console.log('handleChangeState', name, value);
            this.setState({
                form: {
                    ...this.state.form || {},
                    [name]: value
                }
            });
        };
    }

    render() {
        return (
          <Grid>
              <Grid.Column width={12} verticalAlign={'middle'}>
                  <Container fluid>
                      <Header as='h2'>Настройки</Header>
                      <Form className={styles.form}>
                          <Form.Field>
                              <label>Автоматическая настройка</label>
                              <Checkbox toggle onChange={() => this.handleAutoClick.bind(this)}/>
                          </Form.Field>

                          {
                              this.state.isAuto &&
                              <AutoConfig onChange={this.handleChangeState.bind(this)}/> ||
                              <ManualConfig onChange={this.handleChangeState.bind(this)}/>
                          }

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