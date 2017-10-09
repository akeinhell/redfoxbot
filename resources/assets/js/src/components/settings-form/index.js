import escapeRegExp from 'lodash/escapeRegExp';
import filter from 'lodash/filter';
import React, {Component} from 'react';
import {Grid, Header, Search} from 'semantic-ui-react';
import 'semantic-ui-css/semantic.css';

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
        'price': '$$50.81'
    },
    {
        'title': 'Hirthe LLC',
        'description': 'Synergized 6th generation product',
        'image': 'https://s3.amazonaws.com/uifaces/faces/twitter/danro/128.jpg',
        'price': '$$14.47'
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

export default class SearchExampleStandard extends Component {
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
          <Grid>
              <Grid.Column width={8}>
                  <Search
                    loading={isLoading}
                    onResultSelect={this.handleResultSelect}
                    onSearchChange={this.handleSearchChange}
                    results={results}
                    value={value}
                    {...this.props}
                  />
              </Grid.Column>
              <Grid.Column width={8}>
                  <Header>State</Header>
                  <pre>{JSON.stringify(this.state, null, 2)}</pre>
                  <Header>Options</Header>
                  <pre>{JSON.stringify(source, null, 2)}</pre>
              </Grid.Column>
          </Grid>
        );
    }
}