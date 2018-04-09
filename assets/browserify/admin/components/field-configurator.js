'use strict';

import { h, Component, render } from 'preact';
import { bind } from 'decko';
import { htmlgenerate } from '../field-builder/html.js';
import { AddToForm, Required, DefaultValue, Placeholder, Label, Wrap, Choices, ButtonText } from './field-settings.js';
import linkState from 'linkstate';

class FieldConfigurator extends Component {
    constructor(props) {
        super(props);

        this.state = this.getInitialState();
        this.choiceHandlers = {
            "add": this.addChoice,
            "delete": this.deleteChoice,
            "changeLabel": this.changeChoiceLabel,
            "toggleChecked": this.toggleChoiceChecked,
        }
    }

    getInitialState() {
       return {
            fieldType: "",
            fieldLabel: "",
            placeholder: "",
            value: "",
            wrap: true,
            required: false,
            choices: [
            {
                checked: false,
                label: "One",
            },
            {
                checked: false,
                label: "Two",
            },
        ],
       };
    }

    componentWillReceiveProps(props) { 
        this.setState({ fieldType: props.fieldType })
    }

    @bind
    addToForm() {
        const html = htmlgenerate(this.state);
        html_forms.Editor.replaceSelection(html);
    }

    @bind
    addChoice() {
        let arr = this.state.choices;
        arr.push({ checked: false, label: "..." });
        this.setState({choices: arr });
    }

    @bind
    deleteChoice(e) {
        let arr = this.state.choices;
        let index = e.target.parentElement.getAttribute('data-key');
        arr.splice(index, 1);
        this.setState({choices: arr });
    }

    @bind
    changeChoiceLabel(e) {
        let arr = this.state.choices;
        let index = e.target.parentElement.getAttribute('data-key');
        arr[index].label = e.target.value;
        this.setState({choices: arr });
    }

    @bind
    toggleChoiceChecked(e) {
        let arr = this.state.choices;
        let index = e.target.parentElement.getAttribute('data-key');
        arr[index].checked = !arr[index].checked;
        this.setState({choices: arr });
    }

    @bind
    static handleKeyPress(e) {
        // stop RETURN from submitting the parent form.
        if(e.keyCode === 13) {
            e.preventDefault();
        }
    }

    @bind
    handleCancel() {
        // revert back to initial state
        this.setState(this.getInitialState());
        this.props.onCancel();
    }

    render(props, state) {
        if(props.rows.length == 0) {
            return "";
        }

        let formFields = [];

        for(let i=0; i < props.rows.length; i++) {
            switch(props.rows[i]) {
                case "label":
                    formFields.push(<Label value={state.fieldLabel} onChange={linkState(this, 'fieldLabel')}/>);
                break;

                case "placeholder":
                    formFields.push(<Placeholder value={state.placeholder} onChange={linkState(this, 'placeholder')}/>);
                break;

                case "default-value":
                    formFields.push(<DefaultValue value={state.value} onChange={linkState(this, 'value')}/>);
                break;

                case "required":
                    formFields.push(<Required checked={state.required} onChange={linkState(this, 'required')}/>);
                break;

                case "wrap":
                    formFields.push(<Wrap checked={state.wrap} onChange={linkState(this, 'wrap')}/>);
                break;

                case "add-to-form":
                    formFields.push(<AddToForm onSubmit={this.addToForm} onCancel={this.handleCancel} />);
                break;

                case "choices":
                    formFields.push(<Choices multiple={false} choices={state.choices} handlers={this.choiceHandlers} />);
                break;

                case "button-text":
                    formFields.push(<ButtonText value={state.value} onChange={linkState(this, 'value')}/>);
                break;

            }
        }

        return (
            <div class="field-config" onKeyPress={FieldConfigurator.handleKeyPress}>
                {formFields}
            </div>
        )
    }
}

export { FieldConfigurator }
