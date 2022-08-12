echo "Enter your message"
read message
while [ -z "$message" ]
do
echo "You must enter a message"
read message
done
git checkout -b sabbir
git add .
git commit -m"${message}"
git push -u origin sabbir
git checkout master
