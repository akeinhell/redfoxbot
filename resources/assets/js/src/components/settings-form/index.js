import React, {Component} from 'react';
import {Button, Checkbox, Container, Form, Grid, Header} from 'semantic-ui-react';
import 'semantic-ui-css/semantic.css';
import styles from './style.less';
import AutoConfig from './auto-config';
import ManualConfig from './manual-config';



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
            const {value} = target || event.target;
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
                              <ManualConfig
                                onChange={this.handleChangeState.bind(this)}/>
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