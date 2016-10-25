[[ ${HOME} == /Users/* ]] && alias ls='ls -G' || alias ls='ls -ph --color=auto'
alias rm='rm -i'
alias mv='mv -i'
alias cp='cp -i'
eval `dircolors`
declare PROMPT_COMMAND="history -a;history -r"
export HISTTIMEFORMAT="%Y-%m-%d %T "
export HISTCONTROL=ignoredups
shopt -s checkwinsize

if [[ "xterm-256color xterm screen rxvt cygwin" == *"$TERM"* ]] ; then
    bind '"\e[B": history-search-forward'
    bind '"\e[A": history-search-backward'
    set bell-style none
    set show-all-if-ambiguous on
    set completion-ignore-case on
    if [ -f ~/.git-completion.bash ]; then
        #curl -Lf https://raw.githubusercontent.com/git/git/master/contrib/completion/git-completion.bash >~/.git-completion.bash
        source ~/.git-completion.bash
    fi
    if [ -f ~/.git-prompt.sh ]; then
        #curl -Lf https://raw.githubusercontent.com/git/git/master/contrib/completion/git-prompt.sh >~/.git-prompt.sh
        source ~/.git-prompt.sh
        export PS1='\[\e]0;\h:\w\a\]\n\[\e[01;32m\]\u@\h\[\e[00;33m\] \w$(__git_ps1 " (%s)")\n\[\e[1;$((31+3*!$?))m\]\$\[\e[00m\] '
    else
        export PS1='\[\e]0;\h:\w\a\]\n\[\e[01;32m\]\u@\h\[\e[00;33m\] \w\n\[\e[1;$((31+3*!$?))m\]\$\[\e[00m\] '
    fi
    if [ -f ~/.z.sh ]; then
        #curl -Lf https://raw.githubusercontent.com/rupa/z/master/z.sh >~/.z.sh
        source ~/.z.sh
    fi
fi
