# Generated by Django 2.1.7 on 2020-01-06 23:27

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('ranking', '0017_account_updated'),
    ]

    operations = [
        migrations.AddField(
            model_name='module',
            name='has_accounts_infos_update',
            field=models.BooleanField(default=False),
        ),
    ]