import React from 'react'
import {Form, TextField, PasswordField, NumberField, DateField} from 'react-forms-ui'
import {Grid, Panel} from 'react-bootstrap'
// import PropTypes from 'prop-types';
import styles from './style.less';
import classNames from 'classnames';

const validations = {
    myText: {
        required: true,
        minLength: 4,
        maxLength: 10,
    },
    myNumber: {
        required: true,
    },
};

class SettingsForm extends React.Component {
    static propTypes = {
    };

    constructor(props) {
        super(props);
        this.state = {};
    }

    onSubmit() {
        const {values} = this.state;
        console.log(values)
    }

    render() {
        const fieldClasses = 'col-sm-2,col-sm-6,col-sm-4';
        return (
          <Grid fluid className={classNames(styles.form)}>
              <Form state={this.state} setState={this.setState.bind(this)} validations={validations}
                    onSubmit={this.onSubmit}>
                  <Panel header={<h3>My form</h3>}>
                      <TextField id="myText" label="My text" placeholder="Enter some text" classes={fieldClasses}/>
                      <PasswordField id="myPassword" label="My password" classes={fieldClasses}/>
                      <NumberField id="myNumber" label="My number" format="0,0.[00]" classes={fieldClasses}/>
                      <DateField id="myDate" label="My date" classes={fieldClasses}/>
                  </Panel>
              </Form>
          </Grid>
        );
    }
}

export default SettingsForm;