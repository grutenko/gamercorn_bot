import React from 'react';

import List from '@material-ui/core/List';
import ListItem from '@material-ui/core/ListItem';
import ListItemIcon from '@material-ui/core/ListItemIcon';
import ListItemText from '@material-ui/core/ListItemText';
import ArrowRightIcon from '@material-ui/icons/ArrowRight';
import HourglassEmptyIcon from '@material-ui/icons/HourglassEmpty';
import { Button } from '@material-ui/core';
import SkipNextIcon from '@material-ui/icons/SkipNext';
import Alert from '@material-ui/lab/Alert';
import Snackbar from '@material-ui/core/Snackbar';
import FilterNoneIcon from '@material-ui/icons/FilterNone';
import IconButton from '@material-ui/core/IconButton';
import Tooltip from '@material-ui/core/Tooltip';
import {CopyToClipboard} from 'react-copy-to-clipboard';
import Dialog from '@material-ui/core/Dialog';
import DialogActions from '@material-ui/core/DialogActions';
import DialogContent from '@material-ui/core/DialogContent';
import DialogContentText from '@material-ui/core/DialogContentText';
import DialogTitle from '@material-ui/core/DialogTitle';

import {createWs} from './ws';

export default class MusicList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            openError: false,
            openAlert: false,
            items: []
        }
    }

    componentDidMount() {
        this.ws = createWs('music');
        this.ws.onmessage = (e) => {
            let data = JSON.parse(e.data)
            this.setState({
                items: data.data.items.map(item => 
                    {return {
                        customer: item.nickname,
                        author: item.music.match(/^(.+)\s+-/)[1],
                        name: item.music.match(/-\s+(.+)/)[1]
                    }}
                )
            });
        }
        this.ws.onerror = (e) => {
            this.setState({openError: true});
        }
    }

    componentWillUnmount() {
        this.ws.close();
    }

    copyToClipboard(name) {
        if(this.state.openAlert) {
            this.setState({openAlert: false})
        }
        this.setState({openAlert: true});
    }

    onNext() {
        this.ws.send(JSON.stringify({
            channel: "manage",
            type: "remove",
            data: {
                indx: 0
            }
        }));
    }

    closeAlert() {
        this.setState({
            openAlert: false
        })
    }

    render() {
        return <div style={{marginTop: "15px"}}>
            <Dialog open={this.state.openError} onClose={() => this.setState({openError: false})}>
                <DialogTitle id="alert-dialog-title">{"Не удалось подключится к серверу"}</DialogTitle>
                <DialogContent>
                    <DialogContentText id="alert-dialog-description">
                        Видимо он упал и не может поднятся. Напиши кому надо, чтобы понял.
                    </DialogContentText>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => this.setState({openError: false})} color="primary" autoFocus>
                        Ok
                    </Button>
                </DialogActions>
            </Dialog>
            <Snackbar open={this.state.openAlert} autoHideDuration={6000} onClose={this.closeAlert.bind(this)}>
                <Alert onClose={this.closeAlert.bind(this)} severity="success">
                    Название скопировано в буфер обмена!
                </Alert>
            </Snackbar>
            {this.state.items.length > 1
                ? <CopyToClipboard 
                    text={this.state.items[1].author + ' - ' + this.state.items[1].name}
                    onCopy={() => this.copyToClipboard()}
                >
                    <Button
                        startIcon={<SkipNextIcon/>}
                        onClick={this.onNext.bind(this)}
                        disabled={this.state.items.length <= 0}
                    >
                        Дальше
                    </Button>
                </CopyToClipboard>
                : <Button
                    startIcon={<SkipNextIcon/>}
                    onClick={this.onNext.bind(this)}
                    disabled={this.state.items.length <= 0}
                >
                    Дальше
                </Button>
            }
            {this.state.items.length > 0
                ? <List>
                    {this.state.items.map((item, i) =>
                        <ListItem key={i} className="hover-trigger">
                            <ListItemIcon>
                                {i === 0
                                    ? <ArrowRightIcon color="primary" />
                                    :<HourglassEmptyIcon color="secondary" />
                                } 
                            </ListItemIcon>
                            <span style={{marginRight: "25px"}}>#{i+1}</span>
                            <ListItemText
                                primary={item.name}
                                secondary={item.author}
                            />
                            <Tooltip title="Скопировать название в буфер обмена."  placement="left">
                                <CopyToClipboard  text={item.author + ' - ' + item.name} onCopy={() => this.copyToClipboard()}>
                                    <IconButton className="hovered-show--icon">
                                        <FilterNoneIcon/>
                                    </IconButton>
                                </CopyToClipboard>
                            </Tooltip>
                            <ListItemText
                                secondary={item.customer}
                                style={{flex: 'none'}}
                            />
                        </ListItem>
                    )}
                </List>
                : <div style={{paddingTop: "300px"}}>ЖДУ ЗАКАЗЫ</div>
            }
            
        </div>
    }
}