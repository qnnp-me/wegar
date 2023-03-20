# 初始化
shopt -s  extglob
rm -rf /root/.cache/github-code
mkdir -p /root/.cache/github-code
# 新旧版本信息读取
# shellcheck disable=SC2034
OLD_VERSION="$(git for-each-ref --sort=creatordate --format '%(tag)' refs/tags | tail -2 | head -1)"
NEW_VERSION="$(git for-each-ref --sort=creatordate --format '%(tag)' refs/tags | tail -1)"
# 读取上一版本至今的提交信息
#printf "%s\n" "$NEW_VERSION" > /root/.cache/commit.log
#git log --pretty=format:\"%s\" HEAD...$OLD_VERSION >> /root/.cache/commit.log
printf "%s\n" "$(git tag -l --format='%(contents)' "$NEW_VERSION")" >> /root/.cache/commit.log
# 删除白名单以外的文件
rm -rf !(composer.json|src|README.md|doc|LICENSE)
# 拉取 github 代码
git clone https://"$GIT_TOKEN"@github.com/qnnp-me/wegar.git /root/.cache/github-code
# 清空 github 代码
rm -rf /root/.cache/github-code/*
# 复制最新代码至 github
# shellcheck disable=SC2010
ls -a | grep -e '^[^\.]' | awk "{print \"cp -rf ./\"\$1\" /root/.cache/github-code\"}" | sh

# shellcheck disable=SC2164
cd /root/.cache/github-code
# 提交至 github
git config user.email "$GIT_EMAIL"
git config user.name "$GIT_USER"
git add -A
git commit -a -F /root/.cache/commit.log || echo
git tag -a "$NEW_VERSION" -F /root/.cache/commit.log
git push origin main --tags
# 清理残留
rm -rf /root/.cache/github-code
