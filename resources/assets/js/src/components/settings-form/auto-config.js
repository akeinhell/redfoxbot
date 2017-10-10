import React, {Component} from 'react';
import PropTypes from 'prop-types';

export default class AutoConfig extends Component {
    static propTypes = {
        onChange: PropTypes.func,
    };

    render() {
        return <div>
            auto config
        </div>
    }
}